#pragma once

#include <wsjcpp_employees.h>
#include <wsjcpp_yaml.h>
// #include <ctf01d_scoreboard.h>

// ----------------------------------------------------------------------

class Ctf01dServiceDef {
    public:
        Ctf01dServiceDef();

        void setId(const std::string &sServiceId);
        std::string id() const;

        void setName(const std::string &sName);
        std::string name() const;

        void setScriptPath(const std::string &sScriptPath);
        std::string scriptPath() const;

        void setScriptDir(const std::string &sScriptDir);
        std::string scriptDir() const;

        void setEnabled(bool bEnabled);
        bool isEnabled() const;

        void setScriptWaitInSec(int nSec);
        int scriptWaitInSec() const;

        void setTimeSleepBetweenRunScriptsInSec(int nSec);
        int timeSleepBetweenRunScriptsInSec() const;

    private:
        int m_nNum;
        bool m_bEnabled;
        int m_nScriptWaitInSec;
        int m_nTimeSleepBetweenRunScriptsInSec;
        std::string m_sID;
        std::string m_sName;
        std::string m_sScriptPath;
        std::string m_sScriptDir;
};

// ----------------------------------------------------------------------

class Ctf01dTeamDef {
    public:
        Ctf01dTeamDef();

        void setId(const std::string &sId);
        std::string getId() const;

        void setName(const std::string &sName);
        std::string getName() const;

        void setIpAddress(const std::string &sIpAddress);
        std::string ipAddress() const;

        void setActive(bool bActive);
        bool isActive() const;

        void setLogo(const std::string &sLogo);
        std::string logo() const;

        int getLogoLastWriteTime();

    private:
        bool m_bActive;
        std::string m_sTeamID;
        std::string m_sName;
        std::string m_sIpAddress;
        std::string m_sLogo;
        int m_nLogoLastWriteTime;
};

// ----------------------------------------------------------------------

class EmployConfig : public WsjcppEmployBase {
    public:
        EmployConfig();
        ~EmployConfig();
        static std::string name() { return "EmployConfig"; }
        virtual bool init() override;
        virtual bool deinit() override;
        void setWorkDir(const std::string &sWorkDir);
        std::string getWorkDir();

        bool applyConfig();

        // services configuration
        std::vector<Ctf01dServiceDef> &servicesConf();

        // teams configuration
        std::vector<Ctf01dTeamDef> &teamsConf();

        // scoreboard configuration
        int scoreboardPort() const ;
        std::string scoreboardHtmlFolder() const;
        bool scoreboardRandom() const;

        // game configuration
        std::string gameId() const;
        std::string gameName() const;
        int flagTimeliveInMin() const;
        int getBasicCostsStolenFlagInPoints() const;
        int getCostDefenceFlagInPoints10() const;
        int gameStartUTCInSec() const;
        int gameEndUTCInSec() const;

        bool gameHasCoffeeBreak();
        int gameCoffeeBreakStartUTCInSec();
        int gameCoffeeBreakEndUTCInSec();

        void doExtractFilesIfNotExists();

    private:
        bool applyGameConf(WsjcppYaml &yamlConfig);
        bool applyScoreboardConf(WsjcppYaml &yamlConfig);
        bool applyCheckersConf(WsjcppYaml &yamlConfig);
        bool readTeamsConf(WsjcppYaml &yamlConfig);
        void tryLoadFromEnv(const std::string &sEnvName, std::string &sValue, const std::string &sDescription);

        std::string TAG;
        std::string m_sWorkDir;
        bool m_bApplyedConfig;

        int m_nScoreboardPort;
        std::string m_sScoreboardHtmlFolder;
        bool m_bScoreboardRandom;

        // game conf
        int m_nFlagTimeliveInMin;
        int m_nBasicCostsStolenFlagInPoints;
        int m_nCostDefenceFlagInPoints10;
        std::string m_sGameId;
        std::string m_sGameName;
        std::string m_sGameStart;
        std::string m_sGameEnd;
        int m_nGameStartUTCInSec; // UTC in seconds
        int m_nGameEndUTCInSec; // UTC in seconds

        bool m_bHasCoffeeBreak;
        std::string m_sGameCoffeeBreakStart;
        std::string m_sGameCoffeeBreakEnd;
        int m_nGameCoffeeBreakStartUTCInSec; // UTC in seconds
        int m_nGameCoffeeBreakEndUTCInSec; // UTC in seconds

        std::vector<Ctf01dTeamDef> m_vTeamsConf;
        std::vector<Ctf01dServiceDef> m_vServicesConf;
};