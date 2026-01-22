#pragma once

#include "database_file.h"

#include <map>

class DbUuids : public DatabaseFile {
public:
  DbUuids();
  ~DbUuids();

  std::map<std::string, std::string> getAllRecords();
  bool insertUuid(const std::string &sUuid, const std::string &sTypeOfObject);

private:
  std::mutex m_mutex;
  std::string TAG;
};
