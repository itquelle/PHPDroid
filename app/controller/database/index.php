<?php
/**
 * Copyright (c) ItQuelle
 * <www.itquelle.de>
 */

trait Database{

    /**
     * @var PDO
     */

    var $db;

    public function __construct()
    {
        if (db_config["status"] == true) {
            try {
                $this->db = new PDO("mysql:host=" . db_config["host"] . ";dbname=" . db_config["name"] . ";charset=" . db_config["charset"],
                    db_config["user"], db_config["pass"], array(
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => true,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        #@ Options
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8",
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION sql_mode=''"
                    ));
            } catch (PDOException $e) {
                $this->db = NULL;
                die($e->getMessage());
            }
        }
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->db = null;
    }
}