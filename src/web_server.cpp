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


WebServer::WebServer() {
  TAG = "WebServer";
  m_pConfig = findWsjcppEmploy<EmployConfig>();
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

  m_sApiPathPrefix = "/api/v1/";
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

    // if (sRequestPath.rfind(m_sTeamLogoPrefix, 0) == 0) {
    //     std::string sTeamId = sRequestPath.substr(m_nTeamLogoPrefixLength, sRequestPath.length() - m_nTeamLogoPrefixLength);
    //     Ctf01dTeamLogo *pLogo = m_pTeamLogos->findTeamLogo(sTeamId);
    //     if (pLogo == nullptr) {
    //         return 404;
    //     }
    //     resp->SetContentTypeByFilename(pLogo->sFilename.c_str());
    //     return resp->Data(
    //         pLogo->pBuffer,
    //         pLogo->nBufferSize,
    //         true, // nocopy
    //         resp->content_type
    //     );
    // }

    if (sRequestPath.rfind(m_sApiPathPrefix, 0) == 0) {
        return httpPostRequests(req, resp);
        // if (sRequestPath == "/api/v1/game") {
        //     resp->SetContentTypeByFilename("game.json");
        //     std::cout << m_sCacheResponseGameJson << std::endl;
        //     return resp->Data(
        //         (void *)(m_sCacheResponseGameJson.c_str()),
        //         m_sCacheResponseGameJson.length(),
        //         true,
        //         resp->content_type
        //     );
        // } else if (sRequestPath == "/api/v1/scoreboard") {
        //     return this->httpApiV1Scoreboard(req, resp);
        // } else if (sRequestPath == "/api/v1/teams") {
        //     resp->SetContentTypeByFilename("teams.json");
        //     return resp->Data(
        //         (void *)(m_sCacheResponseTeamsJson.c_str()),
        //         m_sCacheResponseTeamsJson.length(),
        //         true,
        //         resp->content_type
        //     );
        // }
        // return this->httpApiV1GetPaths(req, resp);
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

    std::string sResPath = "./data_sample/html" + sRequestPath;
    if (WsjcppResourcesManager::has(sResPath)) {
        WsjcppResourceFile *pFile = WsjcppResourcesManager::get(sResPath);
        resp->SetContentTypeByFilename(sResPath.c_str());
        return resp->Data((void *)pFile->getBuffer(), pFile->getBufferSize(), true, resp->content_type);
    }
    return 404; // Not found
}

// int WebServer::httpApiV1Scoreboard(HttpRequest* req, HttpResponse* resp) {
//     // m_pTeamLogos->updateLastWriteTime();
//     // nlohmann::json jsonScoreboard = m_pConfig->scoreboard()->toJson();
//     // m_pTeamLogos->updateScorebordJson(jsonScoreboard);
//     // std::string sScoreboardJson = jsonScoreboard.dump();
//     // resp->SetContentTypeByFilename("scoreboard.json");
//     // return resp->Data(
//     //     (void *)(sScoreboardJson.c_str()),
//     //     sScoreboardJson.length(),
//     //     false, // nocopy - force copy
//     //     resp->content_type
//     // );
//     return 0;
// }

int WebServer::httpPostRequests(HttpRequest* req, HttpResponse* resp) {
    std::string sRequestPath = normalizeRequestPath(req);

    return 0;
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
