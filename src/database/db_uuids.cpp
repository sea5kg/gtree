#include "db_uuids.h"

#include <wsjcpp_core.h>

// ---------------------------------------------------------------------
// DbUuidsUpdates

class DbUuidsUpdate_000_001 : public DatabaseFileUpdate {
public:
  DbUuidsUpdate_000_001() : DatabaseFileUpdate("", "v001", "Init table uuids") {}
  virtual bool applyUpdate(DatabaseFile *pDatabaseFile) override {
    // IF NOT EXISTS
    return pDatabaseFile->executeQuery("CREATE TABLE uuids ( "
                                       "  id INTEGER PRIMARY KEY AUTOINCREMENT,"
                                       "  uuid VARCHAR(36) NOT NULL,"
                                       "  typeobj VARCHAR(36) NOT NULL,"
                                       "  dt INTEGER NOT NULL"
                                       ");");
  }
};

class DbUuidsUpdate_001_002 : public DatabaseFileUpdate {
public:
  DbUuidsUpdate_001_002() : DatabaseFileUpdate("v001", "v002", "Create uniq index") {}
  virtual bool applyUpdate(DatabaseFile *pDatabaseFile) override {
    return pDatabaseFile->executeQuery("CREATE UNIQUE INDEX IF NOT EXISTS uuids_col_uuid ON uuids (uuid)");
  }
};

// TODO collect and update all uuids from another tables

// ---------------------------------------------------------------------
// DbUuids

DbUuids::DbUuids() : DatabaseFile("uuids.db") {
  TAG = "DbUuids";
  m_vDbUpdates.push_back(new DbUuidsUpdate_000_001());
  m_vDbUpdates.push_back(new DbUuidsUpdate_001_002());
};

DbUuids::~DbUuids() {}

std::map<std::string, std::string> DbUuids::getAllRecords() {
  std::lock_guard<std::mutex> lock(m_mutex);

  std::map<std::string, std::string> mapUuids;
  std::string sSql = "SELECT uuid, typeobj FROM uuids;";
  DatabaseSelectRows cur;
  if (this->selectRows(sSql, cur)) {
    while (cur.next()) {
      mapUuids[cur.getString(0)] = cur.getString(1);
    }
  }
  return mapUuids;
}

bool DbUuids::insertUuid(const std::string &sUuid, const std::string &sTypeOfObject) {
  std::lock_guard<std::mutex> lock(m_mutex);

  DatabaseSqlQueryInsert sql("uuids");
  sql.add("uuid", sUuid);
  sql.add("typeobj", sTypeOfObject);
  sql.add("dt", WsjcppCore::getCurrentTimeInMilliseconds());

  if (!this->executeQuery(sql.getSql())) {
    WsjcppLog::err(TAG, "Could not insert " + sql.getSql());
    return false;
  }
  return true;
}
