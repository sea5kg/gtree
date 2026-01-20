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
  virtual bool init(const std::string &sName, bool bSilent) override;
  virtual bool deinit(const std::string &sName, bool bSilent) override;

  const std::string &getWorkDir();
  int getWebPort();
  const std::string &getDatabaseDir();
  const std::string &getLogDir();
  const std::string &getWebDir();

  // TODO
  void doExtractFilesIfNotExists();

private:
  bool tryLoadFromEnv(const std::string &sEnvName, std::string &sValue, const std::string &sDescription);
  std::string handleRelatedDirPath(const std::string &sDir, const std::string &sDefault);
  bool initLogging(WsjcppYaml &yamlConfig);

  std::string TAG;
  std::string m_sWorkDir;
  std::string m_sLogDir;
  std::string m_sWebDir;
  int m_nWebPort;
  std::string m_sDatabaseDir;
};
