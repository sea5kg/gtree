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

#pragma once

#include <string>
#include <json.hpp>
#include "HttpService.h"
#include <employ_config.h>
#include <employ_users.h>

class WebServer {
public:
  WebServer();
  hv::HttpService *getService();
  int httpGetRequests(HttpRequest* req, HttpResponse* resp);
  int httpPostRequests(HttpRequest* req, HttpResponse* resp);

private:
  std::string normalizeRequestPath(HttpRequest* req);
  std::string jsonrpc20ErrorResponse(int code, const std::string &msg, const std::string &msg_id = "");
  int respError(HttpResponse* resp, int ret_code, const std::string &text);
  int respError(HttpResponse* resp, int ret_code, int code_error, const std::string &msg, const std::string &msg_id);
  int respResult(HttpResponse* resp, const nlohmann::json &result, const std::string &msg_id);
  int doLogin(const nlohmann::json &req, const std::string &msg_id, const std::string &auth, HttpResponse* resp);
  int doLogout(const nlohmann::json &req, const std::string &msg_id, const std::string &auth, HttpResponse* resp);
  int createUser(const nlohmann::json &req, const std::string &msg_id, const std::string &auth, HttpResponse* resp);
  int removeUser(const nlohmann::json &req, const std::string &msg_id, const std::string &auth, HttpResponse* resp);
  int updateUserPassword(const nlohmann::json &req, const std::string &msg_id, const std::string &auth, HttpResponse* resp);

  std::string TAG;
  hv::HttpService *m_pHttpService;
  EmployConfig *m_pConfig;
  EmployUsers *m_pUsers;

  std::string m_sHtmlFolder;

  std::string m_respErrInvalidIncomingJson;
  std::string m_respErrApiOnlyPost;
  std::string m_respErrExpectedJsonObjectInput;
  std::string m_respErrMissingJsonRPCField;
  std::string m_respErrMissingMethodField;
  std::string m_respErrUnknownMethod;
  std::string m_respErrWrongParamsField;
  std::string m_respErrAlreadyAuthorized;
  std::string m_respErrNotAuthorized;
  std::string m_respErrAllowedOnlyAdmin;
};
