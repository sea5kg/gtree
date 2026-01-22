#pragma once

#include <cstring>
#include <mutex>
#include <string>
#include <vector>

class DatabaseFileUpdateInfo {
public:
  DatabaseFileUpdateInfo(
    const std::string &sVersionFrom, const std::string &sVersionTo, const std::string &sDescription
  );
  const std::string &versionFrom() const;
  const std::string &versionTo() const;
  const std::string &description() const;

private:
  std::string m_sVersionFrom;
  std::string m_sVersionTo;
  std::string m_sDescription;
};

class DatabaseFile;

class DatabaseFileUpdate {
public:
  DatabaseFileUpdate(
    const std::string &sVersionFrom, const std::string &sVersionTo, const std::string &sDescription
  );
  const DatabaseFileUpdateInfo &info();
  void setWeight(int nWeight);
  int getWeight();
  virtual bool applyUpdate(DatabaseFile *pDatabaseFile) = 0;

protected:
  std::string TAG;

private:
  DatabaseFileUpdateInfo m_updateInfo;
  int m_nWeight;
};

class DatabaseSelectRows {
public:
  DatabaseSelectRows();
  ~DatabaseSelectRows();
  void setQuery(void *pQuery);
  bool next();
  std::string getString(int nColumnNumber);
  long getLong(int nColumnNumber);

private:
  // hidden type 'sqlite3_stmt *'
  void *m_pQuery;
};

enum class DatabaseSqlQueryType { SELECT, INSERT, UPDATE };

class DatabaseSqlQuery {
public:
  DatabaseSqlQuery(DatabaseSqlQueryType nSqlType, const std::string &sSqlTable);
  bool sel(const std::string &sColumnName);
  bool add(const std::string &sColumnName, const std::string &sValue);
  bool add(const std::string &sColumnName, int nValue);
  bool add(const std::string &sColumnName, long nValue);
  bool where(const std::string &sColumnName, const std::string &sValue);
  bool where(const std::string &sColumnName, int nValue);
  bool where(const std::string &sColumnName, long nValue);

  std::string getSql();
  bool isValid();
  std::string getErrorMessage();

private:
  std::string prepareStringValue(const std::string &sValue);
  bool checkName(const std::string &sColumnName);
  DatabaseSqlQueryType m_nSqlType;
  std::string m_sSqlTable;
  std::string m_sErrorMessage;
  bool m_bValid;

  // query parts
  std::string m_sSqlQuery0;
  std::string m_sSqlQuery1;
  std::string m_sSqlQuery2;
};

class DatabaseSqlQuerySelect : public DatabaseSqlQuery {
public:
  DatabaseSqlQuerySelect(const std::string &sSqlTable);
};

class DatabaseSqlQueryInsert : public DatabaseSqlQuery {
public:
  DatabaseSqlQueryInsert(const std::string &sSqlTable);
};

class DatabaseSqlQueryUpdate : public DatabaseSqlQuery {
public:
  DatabaseSqlQueryUpdate(const std::string &sSqlTable);
};

class DatabaseFile {
public:
  DatabaseFile(const std::string &sFilename);
  ~DatabaseFile();
  std::string getFilename();
  std::string getFileFullpath();
  bool open();
  bool executeQuery(std::string sSqlInsert);
  int selectSumOrCount(std::string sSqlSelectCount);
  bool selectRows(std::string sSqlSelectRows, DatabaseSelectRows &selectRows);

protected:
  bool installUpdates();
  bool insertDbVersion(const DatabaseFileUpdateInfo &info);
  std::vector<DatabaseFileUpdate *> m_vDbUpdates;
  std::string TAG;

private:
  void copyDatabaseToBackup();
  std::mutex m_mutex;

  // hidden type 'sqlite3 *'
  void *m_pDatabaseFile;
  std::string m_sFilename;
  std::string m_sFileFullpath;
  std::string m_sBaseFileBackupFullpath;
  int m_nLastBackupTime;
};
