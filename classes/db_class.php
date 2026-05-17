<?php
/*
TO USE IN config.php

require_once 'folder_name/db_class.php'
$dbh = new DB($host, $port, $dbname, $user, $password);
$query_repo = new QueryRepository($dbh);

WHAT TO DO
Delete ALL prepared statements, executes, and fetches. Move queries into QueryRepository as a public function
Call to get Dept
$query_repo->getByDept($args)
Add more for each query. 

Example in kualiAPI/write/bulk-transfer.php
$select = "SELECT kuali_key, f_name, l_name, school_id, signature, form_id, username FROM user_table WHERE email = :email";
$email = $_SESSION['email'];
$select_stmt = $dbh->prepare($select);
$select_stmt->execute([":email" => $_SESSION['email']]);
$submitter_info = $select_stmt->fetch(PDO::FETCH_ASSOC);

Turns into

$submitter_info = $query_repo->getUserInfo($_SESSION['email']);

This will erase probably 2k lines of code alone. Big project to do, but big payoffs in readability, maintainibility, and overall good practices that I wish I did
Be wary on inserts, updates, or upserts. These may require special functions in DB to do correctly because of implodes.

*/

class DB
{
    private PDO $dbh;

    public function __construct(
        string $host,
        int $port,
        string $dbname,
        string $user,
        string $password
    ) {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        try {
            $this->dbh = new PDO(
                $dsn,
                $user,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            error_log("Error Connecting to Database: " . $e->getMessage());
        }
    }

    public function executeFetchAll(string $query, ...$args): array|false
    {
        try {
            $stmt = $this->dbh->prepare($query);
            $stmt->execute($args);
            return $stmt->fetchAll();
        } catch (PDOException  $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function executeFetch(string $query, ...$args): array|false
    {
        try {
            $stmt = $this->dbh->prepare($query);
            $stmt->execute($args);
            return $stmt->fetch();
        } catch (PDOException  $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function executeRest(string $query, ...$args): bool
    {
        # UPDATE, INSERT, UPSERT (INSERT CONFLICT UPDATE), DELETE
        try {
            $stmt = $this->dbh->prepare($query);
            return $stmt->execute($args);
        } catch (PDOException  $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function executeImplodeQuery(string $query, ...$args) {}

    public function startTransaction()
    {
        try {
            $this->dbh->beginTransaction();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
    public function endTransaction(): void
    {
        try {
            $this->dbh->commit();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
        }
    }

    public function rollBackTransaction(): void
    {
        try {
            $this->dbh->rollBack();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
        }
    }

    public function executeColumn(string $query, mixed ...$args) {
        try {
            $stmt = $this->dbh->prepare($query);
            $stmt->execute($args);
            return $stmt->fetchColumn();
        } catch (PDOException  $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
}

// class QueryRepository
// {
//     public function __construct(private DB $db) {}

//     public function getByEmail(string $email): array|false
//     {
//         return $this->db->executeFetch(
//             "
//             SELECT
//                 kuali_key,
//                 f_name,
//                 l_name,
//                 school_id,
//                 signature,
//                 form_id,
//                 username
//             FROM user_table
//             WHERE email = ?
//             ",
//             $email
//         );
//     }

//     public function getByDept(string $dept_id)
//     {
//         return $this->db->executeFetchAll("SELECT dept_id, dept_name, unnest(custodian) as cust FROM department d WHERE dept_id = ?", $dept_id);
//     }

//     public function getAPIKey(string $email)
//     {
//         return $this->db->executeFetch("SELECT kuali_key FROM user_table WHERE email = ?", $email);
//     }

//     public function getKualiTableInfo()
//     {
//         return $this->db->executeFetch("SELECT * FROM kuali_table");
//     }

class QueryRepository
{
    public function __construct(private DB $db) {}

    public function fetchOne(string $query, mixed ...$args): array|false
    {
        return $this->db->executeFetch($query, ...$args);
    }

    public function fetchAll(string $query, mixed ...$args): array
    {
        return $this->db->executeFetchAll($query, ...$args);
    }

    public function execute(string $query, mixed ...$args): bool
    {
        return $this->db->executeRest($query, ...$args);
    }

    public function fetchColumn(string $query, mixed ...$args)
    {
        return $this->db->executeColumn($query, ...$args);
    }

    public function beginTransaction(): void
    {
        $this->db->startTransaction();
    }

    public function commit(): void
    {
        $this->db->endTransaction();
    }

    public function rollBack(): void
    {
        $this->db->rollBackTransaction();
    }

    public function getCustodians(string $dept_id)
    {
        $query = "SELECT dept_id, dept_name, unnest(custodian) as cust, dept_manager FROM department d WHERE dept_id = ?";

        return $this->db->executeFetchAll($query, $dept_id);
    }

    public function getUserInfo(string $email)
    {
        $query = "SELECT kuali_key, f_name, l_name, school_id, signature, form_id, username FROM user_table WHERE email = ?";

        return $this->db->executeFetch($query, $email);
    }

    public function getCustInfo(string $full_name)
    {
        $query = " select email, form_id, school_id, username, f_name, l_name from user_table where CONCAT(f_name, ' ', l_name) = ?";

        return $this->db->executeFetch($query, $full_name);
    }
}
