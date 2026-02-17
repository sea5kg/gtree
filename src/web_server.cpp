/* MIT License

* Copyright (c) 2019-2025 Evgenii Sopov

* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:

* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.

* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*/

// https://github.com/sea5kg/gtree

#include "web_server.h"

// #include "WebSocketServer.h"
#include "EventLoop.h"
#include "htime.h"
#include "hssl.h"
#include "hlog.h"
#include <regex>
#include <wsjcpp_core.h>

using namespace hv;


GTreeRequestResponse::GTreeRequestResponse(HttpResponse* resp) : m_resp(resp) {

};

int GTreeRequestResponse::response(int ret_http_code, const nlohmann::json &resp_json) {
  std::string text = resp_json.dump();
  m_resp->Data(
    (void *)(text.c_str()),
    text.length(),
    false, // nocopy - force copy
    APPLICATION_JSON
  );
  return ret_http_code;
}

WebServer::WebServer() {
  TAG = "WebServer";
  m_pConfig = findWsjcppEmploy<EmployConfig>();
  m_pUsers = findWsjcppEmploy<EmployUsers>();

  {
    logger_t* pLogger = hv_default_logger();
    // logger_set_max_filesize(pLogger, 102400);
    std::string sLogDirPath = m_pConfig->getLogDir() + "/hv";
    if (!WsjcppCore::dirExists(sLogDirPath)) {
        WsjcppCore::makeDir(sLogDirPath);
    }
    std::string sLogFilePath = sLogDirPath + "/http_" + WsjcppCore::getCurrentTimeForFilename() + ".log";
    logger_set_file(pLogger, sLogFilePath.c_str());
  }
  // m_sTeamLogoPrefix = "/team-logo/";
  // m_nTeamLogoPrefixLength = m_sTeamLogoPrefix.size();

  m_pHttpService = new HttpService();

  // static files
  m_pHttpService->document_root = m_pConfig->getWebDir();
  m_sHtmlFolder = m_pConfig->getWebDir();

  m_pHttpService->GET("*", std::bind(&WebServer::httpGetRequests, this, std::placeholders::_1, std::placeholders::_2));
  m_pHttpService->POST("*", std::bind(&WebServer::httpPostRequests, this, std::placeholders::_1, std::placeholders::_2));
}

hv::HttpService *WebServer::getService() {
    return m_pHttpService;
}

int WebServer::httpGetRequests(HttpRequest* req, HttpResponse* resp) {

  // remove get params from path
  std::string sRequestPath = normalizeRequestPath(req);

  if (sRequestPath == "/api" || sRequestPath.rfind("/api/", 0) == 0) {
    return httpPostRequests(req, resp);
  }

  if (sRequestPath == "/") {
    sRequestPath = "/index.html";
  }

  // TODO
  WsjcppLog::info(TAG, "Request path: " + sRequestPath);
  std::string sFilePath = sRequestPath = WsjcppCore::doNormalizePath(m_sHtmlFolder + "/" + sRequestPath);
  if (WsjcppCore::fileExists(sFilePath)) { // TODO check the file exists not dir
    return resp->File(sFilePath.c_str());
  }

  // TODO cache
  std::string sResPath = "./data_sample/html" + sRequestPath;
  if (WsjcppResourcesManager::has(sResPath)) {
    WsjcppResourceFile *pFile = WsjcppResourcesManager::get(sResPath);
    resp->SetContentTypeByFilename(sResPath.c_str());
    return resp->Data((void *)pFile->getBuffer(), pFile->getBufferSize(), true, resp->content_type);
  }
  return 404; // Not found
}

int WebServer::httpPostRequests(HttpRequest* req, HttpResponse* resp) {
  std::string sRequestPath = normalizeRequestPath(req);

  auto context = std::make_shared<GTreeRequestResponse>(resp);

  auto now = std::chrono::system_clock::now().time_since_epoch();
  int nCurrentTimeSec = std::chrono::duration_cast<std::chrono::seconds>(now).count();

  std::string auth = req->GetHeader("Authorization");
  context->setAuth(auth);
  // WsjcppLog::info(TAG, "auth = " + auth);

  if (req->method != HTTP_POST) {
    return context->error403(ERR_01001_ONLY_POST_REQUESTS);
  }

  nlohmann::json req_json_body;
  try {
    req_json_body = nlohmann::json::parse(req->body);
  } catch (nlohmann::json::parse_error& error) {
    // std::cerr << "Parse error at byte: " << error.byte << std::endl;
    return context->error400(ERR_01002_INVALID_INCOMING_JSON);
  }

  if (!req_json_body.is_object()) {
    return context->error400(ERR_01003_EXPECTED_JSON_INPUT);
  }

  if (!req_json_body["jsonrpc"].is_string()) {
    return context->error400(ERR_01004_MISSING_FIELD_JSONRPC);
  }

  if (!req_json_body["method"].is_string()) {
    // std::cerr << "Not found field method " << std::endl;
    return context->error400(ERR_01005_MISSING_FIELD_METHOD);
  }

  std::string method = req_json_body["method"];
  std::string msg_id = "";
  if (req_json_body["id"].is_string()) {
    msg_id = req_json_body["id"];
  }

  if (method == "checkAuth") {
    return checkAuth(req_json_body, context);
  } else if (method == "doLogin") {
    return doLogin(req_json_body, context);
  } else if (method == "doLogout") {
    return doLogout(req_json_body, context);
  } else if (method == "createUser") {
    return createUser(req_json_body, resp, context);
  } else if (method == "removeUser") {
    return removeUser(req_json_body, resp, context);
  } else if (method == "resetUserPassword") {
    return resetUserPassword(req_json_body, resp, context);
  } else if (method == "changePassword") {
    return changePassword(req_json_body, resp, context);
  }

  return context->error404(ERR_01006_UNKNOWN_METHOD);
}

std::string WebServer::normalizeRequestPath(HttpRequest* req) {
  std::string sOriginalRequestPath = req->path;
  std::string sRequestPath;
  // remove get params from path
  std::size_t nFoundGetParams = sOriginalRequestPath.rfind("?");
  if (nFoundGetParams != std::string::npos) {
    sRequestPath = sOriginalRequestPath.substr(0, nFoundGetParams);
  } else {
    sRequestPath = sOriginalRequestPath;
  }
  sRequestPath = WsjcppCore::doNormalizePath(sRequestPath);
  return sRequestPath;
}

std::string WebServer::jsonrpc20ErrorResponse(int code, const std::string &msg, const std::string &msg_id) {
   // TODO data
  nlohmann::json resp_json;
  resp_json["jsonrpc"] = "2.0";
  resp_json["error"] = nlohmann::json();
  resp_json["error"]["code"] = code;
  resp_json["error"]["message"] = msg;
  if (msg_id != "") {
    resp_json["id"] = msg_id;
  }
   // "error":{
   //    "code": 10,
   //    "message": "Unauthorized action",
   //    "data":[
   //       {
   //          "code": 2,
   //          "message":"Denied privileged API access for uid=XXX gid=XXX"
   //       }
   //    ]
   // "id":"5e273ec0-3e3b-4a81-90ec-aeee3d38073f"
  return resp_json.dump();
}

int WebServer::respError(HttpResponse* resp, int ret_code, const std::string &text) {
  resp->Data(
    (void *)(text.c_str()),
    text.length(),
    false, // nocopy - force copy
    APPLICATION_JSON
  );
  return ret_code;
}

int WebServer::respError(HttpResponse* resp, int ret_code, int code_error, const std::string &msg, const std::string &msg_id) {
  std::string text = jsonrpc20ErrorResponse(code_error, msg, msg_id);
  return respError(resp, ret_code, text);
}

int WebServer::checkAuth(const nlohmann::json &req, std::shared_ptr<gtree::HandleContext> context) {
  // auth
  UserSession req_session = m_pUsers->findSession(context->getAuth());
  if (req_session.uuid == "") {
    return context->error401(ERR_01009_NOT_AUTHORIZED);
  }

  nlohmann::json result;
  result["session"] = req_session.uuid;
  result["server_time"] = WsjcppCore::getCurrentTimeInSeconds();
  return context->success(result);
}

int WebServer::doLogin(const nlohmann::json &req, std::shared_ptr<gtree::HandleContext> context) {
  // auth
  UserSession req_session = m_pUsers->findSession(context->getAuth());
  if (req_session.uuid != "") {
    return context->error401(ERR_01008_YOU_ALREADY_AUTHORIZED);
  }

  if (!req["params"].is_object()) {
    return context->error400(ERR_01007_MISSING_OR_WRONG_FIELD_PARAMS);
  }

  if (!req["params"]["email"].is_string()) {
    return context->error400(ERR_10012_MISSING_FIELD_EMAIL);
  }
  if (!req["params"]["pass"].is_string()) {
    return context->error400(ERR_10022_MISSING_FIELD_PASS);
  }
  std::string email = req["params"]["email"];
  std::string pass = req["params"]["pass"];
  UserSession session = m_pUsers->doLogin(email, pass);
  if (session.uuid == "") {
    return context->error401(ERR_10021_COULD_NOT_LOGIN);
  }

  nlohmann::json result;
  result["session"] = session.uuid;
  nlohmann::json user;
  user["email"] = session.user.email;
  user["role"] = session.user.role;
  result["user"] = user;
  result["expired_at"] = session.expired_at;
  result["server_time"] = WsjcppCore::getCurrentTimeInSeconds();
  return context->success(result);
}

int WebServer::doLogout(const nlohmann::json &req, std::shared_ptr<gtree::HandleContext> context) {
  UserSession req_session = m_pUsers->findSession(context->getAuth());

  if (req_session.uuid == "") {
    return context->error401(ERR_01009_NOT_AUTHORIZED);
  }

  if (!m_pUsers->doLogout(req_session.uuid)) {
    return context->error401(ERR_10011_COULD_NOT_DID_LOGOUT);
  }

  nlohmann::json result;
  result["removed_session"] = req_session.uuid;
  return context->success(result);
}

int WebServer::createUser(const nlohmann::json &req, HttpResponse* resp, std::shared_ptr<gtree::HandleContext> context) {
  UserSession req_session = m_pUsers->findSession(context->getAuth());

  if (req_session.uuid == "") {
    return context->error401(ERR_01009_NOT_AUTHORIZED);
  }

  if (req_session.user.role != "admin") {
    return context->error403(ERR_01010_ALLOWED_ONLY_FOR_ADMIN);
  }

  if (!req["params"].is_object()) {
    // std::cerr << "Not found field method " << std::endl;
    return context->error400(ERR_01007_MISSING_OR_WRONG_FIELD_PARAMS);
  }

  if (!req["params"]["email"].is_string()) {
    return respError(resp, 400, 10004, "Missing field 'email' or wrong type", context->getMessageId());
  }
  if (!req["params"]["pass"].is_string()) {
    return respError(resp, 400, 10005, "Missing field 'pass' or wrong type", context->getMessageId());
  }
  if (!req["params"]["role"].is_string()) {
    return respError(resp, 400, 10006, "Missing field 'role' or wrong type", context->getMessageId());
  }

  std::string email = req["params"]["email"];
  std::string pass = req["params"]["pass"];
  std::string role = req["params"]["role"];


  std::string error;

  if (!m_pUsers->createUser(email, pass, role, error)) {
    return respError(resp, 401, 10007, error, context->getMessageId());
  }

  nlohmann::json result;
  result["email"] = email;
  result["role"] = role;
  return context->success(result);
}

int WebServer::removeUser(const nlohmann::json &req, HttpResponse* resp, std::shared_ptr<gtree::HandleContext> context) {
  UserSession req_session = m_pUsers->findSession(context->getAuth());

  if (req_session.uuid == "") {
    return context->error401(ERR_01009_NOT_AUTHORIZED);
  }

  if (req_session.user.role != "admin") {
    return context->error403(ERR_01010_ALLOWED_ONLY_FOR_ADMIN);
  }

  if (!req["params"].is_object()) {
    // std::cerr << "Not found field method " << std::endl;
    return context->error400(ERR_01007_MISSING_OR_WRONG_FIELD_PARAMS);
  }

  if (!req["params"]["email"].is_string()) {
    return respError(resp, 400, 10004, "Missing field 'email' or wrong type", context->getMessageId());
  }

  std::string email = req["params"]["email"];

  if (req_session.user.email == email) {
    return respError(resp, 403, 10008, "You can not delete yourself", context->getMessageId());
  }

  std::string error;
  if (!m_pUsers->removeUser(email, error)) {
    return context->error403(gtree::ResponseError(10009, error, "", ""));
  }

  // TODO remove from uuids
  // TODO remove from sessions

  nlohmann::json result;
  result["email"] = email;
  return context->success(result);
}

int WebServer::resetUserPassword(const nlohmann::json &req, HttpResponse* resp, std::shared_ptr<gtree::HandleContext> context) {
  UserSession req_session = m_pUsers->findSession(context->getAuth());

  if (req_session.uuid == "") {
    return context->error401(ERR_01009_NOT_AUTHORIZED);
  }

  if (req_session.user.role != "admin") {
    return context->error403(ERR_10023_ONLY_ADMIN_CAN_RESET_PASSWORD);
  }

  if (!req["params"].is_object()) {
    // std::cerr << "Not found field method " << std::endl;
    return context->error400(ERR_01007_MISSING_OR_WRONG_FIELD_PARAMS);
  }

  if (!req["params"]["email"].is_string()) {
    return respError(resp, 400, 10004, "Missing field 'email' or wrong type", context->getMessageId());
  }

  if (!req["params"]["pass"].is_string()) {
    return respError(resp, 400, 10004, "Missing field 'pass' or wrong type", context->getMessageId());
  }

  std::string email = req["params"]["email"];
  std::string pass = req["params"]["pass"];

  std::string error;
  if (!m_pUsers->resetUserPassword(email, pass, error)) {
    return respError(resp, 401, 10011, error, context->getMessageId());
  }

  nlohmann::json result;
  result["email"] = email;
  return context->success(result);
}

int WebServer::changePassword(const nlohmann::json &req, HttpResponse* resp, std::shared_ptr<gtree::HandleContext> context) {
  UserSession req_session = m_pUsers->findSession(context->getAuth());

  if (req_session.uuid == "") {
    return context->error401(ERR_01009_NOT_AUTHORIZED);
  }

  if (!req["params"].is_object()) {
    return context->error400(ERR_01007_MISSING_OR_WRONG_FIELD_PARAMS);
  }

  if (!req["params"]["old_pass"].is_string()) {
    return context->error400(ERR_10014_MISSING_FIELD_OLD_PASS);
  }

  if (!req["params"]["new_pass"].is_string()) {
    return context->error400(ERR_10013_MISSING_FIELD_NEW_PASS);
  }

  std::string email = req_session.user.email;
  std::string old_pass = req["params"]["old_pass"];
  std::string new_pass = req["params"]["new_pass"];

  std::string error;
  if (!m_pUsers->changePassword(email, old_pass, new_pass, error)) {
    return respError(resp, 401, 10011, error, context->getMessageId());
  }

  nlohmann::json result;
  result["email"] = email;
  return context->success(result);
}