<?php

class db
{
    public function __construct(string $host, string $user, string $pwd, string $charset = "utf8")
    {
        $this->db = null;

        $opt  = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => TRUE,
        ];

        $dsn = 'mysql:host='.$host.';charset='.$charset;

        $this->db = new PDO($dsn, $user, $pwd, $opt);
    }

    public function query(string $sql, $return=false)
    {
        $statement = $this->db->prepare($sql);

        if(!($res = $statement->execute()))
            return null;

        if($statement->rowCount() == 0)
            return [];

        if($return)
            return $statement->fetchAll();
        else
            return $res;
    }

    public function lastInsertId()
    {
        return $this->db->lastInsertId();
    }

    //######################################################################

    //! объект PDO
    private $db = null;
}
