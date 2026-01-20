#include <wsjcpp_core.h>
#include <employ_config.h>
#include "web_server.h"
#include "WebSocketServer.h"  // libhv

int main(int argc, const char* argv[]) {
    WsjcppLog::setEnableLogFile(false);

    WsjcppEmployeesInit empls({}, false);
    if (!empls.inited) {
        return -1;
    }

    EmployConfig *pConfig = findWsjcppEmploy<EmployConfig>();

    WsjcppLog::info("main", "Start web server on http://localhost:" + std::to_string(pConfig->getWebPort()));
    WebServer httpServer;
    hv::HttpService *pRouter = httpServer.getService();
    hv::HttpServer server(pRouter);
    server.setPort(pConfig->getWebPort());
    server.setThreadNum(1);
    server.run();

    // getLogDir()

    // websocket_server_t server;
    // server.service = pRouter;
    // server.port = 12345;
    // // server.ws = pWs;
    // websocket_server_run(&server);

    return 0;
}
