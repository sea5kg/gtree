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

#include "request_handler.h"

namespace gtree {

// HandleContext

void HandleContext::setMessageId(const std::string &msg_id) {
  m_msg_id = msg_id;
}

const std::string &HandleContext::getMessageId() {
  return m_msg_id;
}

void HandleContext::setAuth(const std::string &auth) {
  m_auth = auth;
}

const std::string &HandleContext::getAuth() {
  return m_auth;
}

int HandleContext::success(const nlohmann::json &result) {
  nlohmann::json resp_json = prepareResponseJson();
  resp_json["result"] = result;
  return response(200, resp_json);
}

int HandleContext::failed(int ret_http_code, const ResponseError &error) {
  nlohmann::json resp_json = prepareResponseJson();
  // resp_json["error"] = nlohmann::json();
  resp_json["error"]["code"] = error.code;
  resp_json["error"]["message"] = error.msg;
  resp_json["error"]["message_ru"] = error.msg_ru;
  // TODO data
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
  return response(ret_http_code, resp_json);
}

int HandleContext::error400(const ResponseError &error) {
  return failed(400, error);
}

int HandleContext::error401(const ResponseError &error) {
  return failed(401, error);
}

int HandleContext::error403(const ResponseError &error) {
  return failed(403, error);
}

int HandleContext::error404(const ResponseError &error) {
  return failed(404, error);
}

nlohmann::json HandleContext::prepareResponseJson() {
  nlohmann::json resp_json;
  resp_json["jsonrpc"] = "2.0";
  if (m_msg_id != "") {
    resp_json["id"] = m_msg_id;
  }
  return resp_json;
}

// RequestHandler

RequestHandler::RequestHandler(const std::string &method_name)
  : m_method_name(method_name) {
};

const std::string &RequestHandler::method() {
  return m_method_name;
};

} // namespace gtree