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
    // m_pEmployFlags = findWsjcppEmploy<EmployFlags>();
    // m_pEmployDatabase = findWsjcppEmploy<EmployDatabase>();
    // m_pTeamLogos = findWsjcppEmploy<EmployTeamLogos>();

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
    m_sTeamLogoPrefix = "/team-logo/";
    m_nTeamLogoPrefixLength = m_sTeamLogoPrefix.size();

    m_jsonGame["teams"] = nlohmann::json::array();
    m_jsonGame["services"] = nlohmann::json::array();

    m_sCacheResponseGameJson = m_jsonGame.dump();
    m_sCacheResponseTeamsJson = m_jsonTeams.dump();
    // m_sCacheResponseServicesJson =

    m_pHttpService = new HttpService();

    // static files
    m_pHttpService->document_root = "./html";

    // m_pHttpService->GET("/api/", std::bind(&WebServer::httpApiV1GetPaths, this, std::placeholders::_1, std::placeholders::_2));
    // m_pHttpService->GET("/api/v1/", std::bind(&WebServer::httpApiV1GetPaths, this, std::placeholders::_1, std::placeholders::_2));

    m_pHttpService->GET("*", std::bind(&WebServer::httpWebFolder, this, std::placeholders::_1, std::placeholders::_2));
    // m_pHttpService->GET("/admin*", std::bind(&WebServer::httpAdmin, this, std::placeholders::_1, std::placeholders::_2));


    // m_pHttpService->GET("/get", [](HttpRequest* req, HttpResponse* resp) {
    //     resp->json["origin"] = req->client_addr.ip;
    //     resp->json["url"] = req->url;
    //     resp->json["args"] = req->query_params;
    //     resp->json["headers"] = req->headers;
    //     return 200;
    // });
}

hv::HttpService *WebServer::getService() {
    return m_pHttpService;
}

int WebServer::httpApiV1GetPaths(HttpRequest* req, HttpResponse* resp) {
    return resp->Json(m_pHttpService->Paths());
}

int WebServer::httpAdmin(HttpRequest* req, HttpResponse* resp) {
    std::string str = req->path + " match /admin*";
    return resp->String(str);
}

int WebServer::httpWebFolder(HttpRequest* req, HttpResponse* resp) {
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

    // WsjcppLog::info(TAG, "sRequestPath = " + sRequestPath);
    if (sRequestPath == "/flag") {
        return this->httpApiV1Flag(req, resp);
    }

    if (sRequestPath.rfind(m_sTeamLogoPrefix, 0) == 0) {
        std::string sTeamId = sRequestPath.substr(m_nTeamLogoPrefixLength, sRequestPath.length() - m_nTeamLogoPrefixLength);
        // Ctf01dTeamLogo *pLogo = m_pTeamLogos->findTeamLogo(sTeamId);
        // if (pLogo == nullptr) {
        //     return 404;
        // }
        // resp->SetContentTypeByFilename(pLogo->sFilename.c_str());
        // return resp->Data(
        //     pLogo->pBuffer,
        //     pLogo->nBufferSize,
        //     true, // nocopy
        //     resp->content_type
        // );
    }

    if (sRequestPath.rfind(m_sApiPathPrefix, 0) == 0) {
        if (sRequestPath == "/api/v1/game") {
            resp->SetContentTypeByFilename("game.json");
            std::cout << m_sCacheResponseGameJson << std::endl;
            return resp->Data(
                (void *)(m_sCacheResponseGameJson.c_str()),
                m_sCacheResponseGameJson.length(),
                true,
                resp->content_type
            );
        } else if (sRequestPath == "/api/v1/scoreboard") {
            return this->httpApiV1Scoreboard(req, resp);
        } else if (sRequestPath == "/api/v1/teams") {
            resp->SetContentTypeByFilename("teams.json");
            return resp->Data(
                (void *)(m_sCacheResponseTeamsJson.c_str()),
                m_sCacheResponseTeamsJson.length(),
                true,
                resp->content_type
            );
        }
        return this->httpApiV1GetPaths(req, resp);
    }

    if (sRequestPath == "/") {
        sRequestPath = "/index.html";
    }

    // TODO
    WsjcppLog::info(TAG, "Request path: " + sRequestPath);
    std::string sFilePath = sRequestPath = WsjcppCore::doNormalizePath(m_sScoreboardHtmlFolder + "/" + sRequestPath);
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

// int WebServer::admin(const std::string &sWorkerId, WsjcppLightWebHttpRequest *pRequest){
//     std::string _tag = TAG + "-" + sWorkerId;
//     std::string sRequestPath = pRequest->getRequestPath();
//     sRequestPath = WsjcppCore::doNormalizePath(sRequestPath);

//     WsjcppLightWebHttpResponse response(pRequest->getSockFd());

//     // Log::warn(_tag, pRequest->requestPath());

//     if (sRequestPath == "/") {
//         sRequestPath = "/index.html";
//     }

//     std::string sFilePath = m_sWebFolder + sRequestPath;



//     // Log::warn(_tag, "Response File " + sFilePath);
//     response.cacheSec(60).ok().sendFile(sFilePath);
//     return true;
// }


int WebServer::httpApiV1Flag(HttpRequest* req, HttpResponse* resp) {
    auto now = std::chrono::system_clock::now().time_since_epoch();
    int nCurrentTimeSec = std::chrono::duration_cast<std::chrono::seconds>(now).count();

    // if (nCurrentTimeSec < m_pConfig->gameStartUTCInSec()) {
    //     const std::string sErrorMsg = "Error(-8): Game not started yet";
    //     WsjcppLog::err(TAG, sErrorMsg);
    //     return resp->String(sErrorMsg, 400);
    // }

    std::string sResponse = "Accepted: Recieved flag {} from {} (Accepted)";
    WsjcppLog::ok(TAG, sResponse);
    return resp->Data((void *)(sResponse.c_str()), sResponse.size(), false, TEXT_PLAIN);
}

int WebServer::httpApiV1Scoreboard(HttpRequest* req, HttpResponse* resp) {
    // m_pTeamLogos->updateLastWriteTime();
    // nlohmann::json jsonScoreboard = m_pConfig->scoreboard()->toJson();
    // m_pTeamLogos->updateScorebordJson(jsonScoreboard);
    // std::string sScoreboardJson = jsonScoreboard.dump();
    // resp->SetContentTypeByFilename("scoreboard.json");
    // return resp->Data(
    //     (void *)(sScoreboardJson.c_str()),
    //     sScoreboardJson.length(),
    //     false, // nocopy - force copy
    //     resp->content_type
    // );
    return 0;
}