<?php
namespace NahroTo\DatabaseManager;

class Database {
    
    /** @var string */
    private $host;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var string */
    private $name;

    public function __construct(string $host, string $username, string $password, string $name) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->name = $name;
    }

    public function getHost(): string {
        return $this->host;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getName(): string {
        return $this->name;
    }
}