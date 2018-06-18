<?php
class DatabaseManager {

    /** @var Database The database this class uses. */
    private $database;

    /** @var PDO */
    private $pdo;

    /** @var array */
    private $pdoAttributes = [];

    public function __construct(Database $database) {
        $this->database = $database;
    }

    /**
     * Starts a new connection with the database.
     * Must be called before {@see DatabaseManager::query()}
     */
    public function start(): void {
        $database = $this->getDatabase();

        $databaseHost = $database->getHost();
        $databaseUsername = $database->getUsername();
        $databasePassword = $database->getPassword();
        $databaseName = $database->getName();

        $pdo = new PDO(
            "mysql:host=$databaseHost;dbname=$databaseName",
            $databaseUsername,
            $databasePassword
        );

        foreach ($this->pdoAttributes as $attribute => $value) {
            $pdo->setAttribute($attribute, $value);
        }

        $this->pdo = $pdo;
    }

    /**
     * Queries the database.
     * @param string $sqlQuery The query in SQL.
     * @param array $bindedParameters (optional) Array of binded parameters that are marked with '?' in the query.
     * @return array The query results.
     */
    public function query(string $sqlQuery, array $bindedParameters = null): array  {
        if (is_null($this->pdo)) {
            throw new Exception("query(..) called without calling start() before.");
        }
        $pdoStatement = $pdo->prepare($sqlQuery);
        if (!is_null($bindedParameters)) {
            foreach ($bindedParameters as $index => $bindedParameter) {
                $pdoStatement->bindParam($index + 1, $bindedParameter);
            }
        }
        $pdoStatement->execute();
        return $pdoStatement->fetchAll();
    }

    /**
     * Stops the database connection.
     * @see DatabaseManager::start()
     */
    public function stop(): void {
        $this->pdo = null;
    }

    public function setDatabase(Database $database): void {
        $this->database = $database;
    }

    /**
     * @return Database The database.
     */
    public function getDatabase(): Database {
        return $this->$database;
    }

    /**
     * Sets the pdo attributes. {@see DatabaseManager::start()} needs to be called
     * afterwards in order to apply the changes.
     */
    public function setPdoAttributes(array $pdoAttributes): void {
        $this->pdoAttributes = $pdoAttributes;
    }

    /**
     * @return array The configured PDO attributes.
     */
    public function getPdoAttributes(): array {
        return $this->pdoAttributes;
    }
}