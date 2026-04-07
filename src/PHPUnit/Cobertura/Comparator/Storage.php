<?php

/**
 * @author    andrey-tech
 * @copyright 2026 andrey-tech
 * @link      https://github.com/andrey-tech/
 * @license   MIT
 */

declare(strict_types=1);

namespace AndreyTech\PHPUnit\Cobertura\Comparator;

use AndreyTech\PHPUnit\Cobertura\Comparator\Parser\Metrics;
use PDO;
use PDOException;
use stdClass;

use const SQLITE3_FLOAT;
use const SQLITE3_INTEGER;
use const SQLITE3_TEXT;

final class Storage
{
    private const string REGRESSIONS_SQL = <<< 'SQL'
        WITH RegressedClasses AS (
            SELECT 
                m1.class_name,
                m0.class_line_rate AS old_class_line_rate,
                m1.class_line_rate AS new_class_line_rate,
                m0.class_branch_rate AS old_class_branch_rate,
                m1.class_branch_rate AS new_class_branch_rate
            FROM metrics m1
            JOIN metrics m0
                ON m1.class_name = m0.class_name
            WHERE m1.version = 1 
              AND m0.version = 0
              AND (m1.class_line_rate < m0.class_line_rate
                  OR m1.class_branch_rate < m0.class_branch_rate
              )
            GROUP BY m1.class_name 
        )

        SELECT 
            m.file,
            m.class_name,
            'old' AS class_status,
            m.method_name,
            (SELECT old.method_line_rate
             FROM metrics old 
             WHERE old.class_name = m.class_name
                 AND old.method_name = m.method_name
                 AND old.version = 0
            ) AS old_method_line_rate,
            m.method_line_rate AS new_method_line_rate,
            (SELECT old.method_branch_rate
             FROM metrics old 
             WHERE old.class_name = m.class_name
                 AND old.method_name = m.method_name
                 AND old.version = 0
            ) AS old_method_branch_rate,
            m.method_branch_rate AS new_method_branch_rate,
            rc.old_class_line_rate,
            rc.new_class_line_rate,
            rc.old_class_branch_rate,
            rc.new_class_branch_rate,
            CASE 
                WHEN NOT EXISTS (
                    SELECT 1 FROM metrics old
                    WHERE old.method_name = m.method_name
                        AND old.class_name = m.class_name
                        AND old.version = 0
                    ) 
                THEN 'new'
                ELSE 'old'
            END AS method_status
        FROM metrics m
        JOIN RegressedClasses rc
            ON m.class_name = rc.class_name
        WHERE m.version = 1
        
        UNION ALL
        
        SELECT 
            old.file,
            old.class_name,
            'old' AS class_status,
            old.method_name,
            old.method_line_rate AS old_method_line_rate,
            NULL AS new_method_line_rate,
            old.method_branch_rate AS old_method_branch_rate,
            NULL AS new_method_branch_rate,
            rc.old_class_line_rate,
            rc.new_class_line_rate,
            rc.old_class_branch_rate,
            rc.new_class_branch_rate,
            'del' AS method_status
        FROM metrics old
        JOIN RegressedClasses rc
            ON old.class_name = rc.class_name
        WHERE old.version = 0
          AND NOT EXISTS (
              SELECT 1 FROM metrics current 
              WHERE current.class_name = old.class_name 
                AND current.method_name = old.method_name 
                AND current.version = 1
          )
    SQL;

    private PDO $pdo;

    public function __construct()
    {
        $this->initDB();
        $this->initTable();
    }

    /**
     * @param iterable<Metrics> $metricsList
     *
     * @throws PDOException
     */
    public function store(iterable $metricsList, int $version): void
    {
        $this->pdo->exec('BEGIN');

        foreach ($metricsList as $metrics) {
            $this->insert($metrics, $version);
        }

        $this->pdo->exec('COMMIT');
    }

    /**
     * @return array<stdClass>
     *
     * @throws PDOException
     */
    public function getRegressions(): array
    {
        return $this->pdo->query(self::REGRESSIONS_SQL)->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @throws PDOException
     */
    private function insert(Metrics $metrics, int $version): void
    {
        $stmt = $this->pdo->prepare(<<< 'SQL'
            INSERT INTO metrics
            (
                version, file, class_name, class_line_rate, class_branch_rate, 
                method_name, method_line_rate, method_branch_rate
            ) VALUES (:version, :file, :class, :c_line, :c_branch, :method, :m_line, :m_branch)
            SQL
        );

        $stmt->bindValue(':version', $version, SQLITE3_INTEGER);
        $stmt->bindValue(':file', $metrics->file, SQLITE3_TEXT);
        $stmt->bindValue(':class', $metrics->className, SQLITE3_TEXT);
        $stmt->bindValue(':c_line', $metrics->classLineRate, SQLITE3_FLOAT);
        $stmt->bindValue(':c_branch', $metrics->classBranchRate, SQLITE3_FLOAT);
        $stmt->bindValue(':method', $metrics->methodName, SQLITE3_TEXT);
        $stmt->bindValue(':m_line', $metrics->methodLineRate, SQLITE3_FLOAT);
        $stmt->bindValue(':m_branch', $metrics->methodBranchRate, SQLITE3_FLOAT);

        $stmt->execute();
    }

    /**
     * @throws PDOException
     */
    private function initDB(): void
    {
        $this->pdo = new PDO('sqlite::memory:');

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('PRAGMA synchronous = OFF');
    }

    /**
     * @throws PDOException
     */
    private function initTable(): void
    {
        $this->pdo->exec(<<< 'SQL'
            CREATE TABLE IF NOT EXISTS metrics (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                version INTEGER,
                file TEXT,
                class_name TEXT,
                class_line_rate REAL,
                class_branch_rate REAL,
                method_name TEXT,
                method_line_rate REAL,
                method_branch_rate REAL
            )
            SQL
        );

        $this->pdo->exec('CREATE INDEX idx_version_class ON metrics (version, class_name)');
        $this->pdo->exec('CREATE INDEX idx_version_class_method ON metrics (version, class_name, method_name)');
    }
}
