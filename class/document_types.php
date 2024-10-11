<?php
include_once '../connect/db_connect.php';
include_once 'Encryption.php';

class DocumentTypes
{
    private $db;
    private $connection;
    private $encryption;

    public function __construct()
    {
        $this->db = new DBConnect();
        $this->connection = $this->db->getConnection();
        $this->encryption = new Encryption();
    }

    public function getDocumentTypeAll()
    {
        $query = 'SELECT document_type_id, document_type_code, dt_year, dt_from, dt_to, sequence 
                  FROM cm_sap.tb_document_types WHERE is_deleted = false ORDER BY created_at ASC';
        $result = pg_prepare($this->connection, "get_all_document_types", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting all document types.');
        }
        $result = pg_execute($this->connection, "get_all_document_types", []);
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting all document types.');
        }
        $documentTypes = pg_fetch_all($result);
        if ($documentTypes === false) {
            return [];
        }
        foreach ($documentTypes as &$documentType) {
            $documentType['document_type_id'] = $this->encryption->encrypt($documentType['document_type_id']);
        }
        return $documentTypes;
    }

    public function getDocumentType($encryptedDocumentTypeId)
    {
        $documentTypeId = $this->encryption->decrypt($encryptedDocumentTypeId);
        $query = 'SELECT document_type_id, document_type_code, dt_year, dt_from, dt_to, sequence 
                  FROM cm_sap.tb_document_types WHERE document_type_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "get_document_type_by_id", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for getting document type by ID.');
        }
        $result = pg_execute($this->connection, "get_document_type_by_id", array($documentTypeId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for getting document type by ID.');
        }
        $documentType = pg_fetch_assoc($result);
        if ($documentType === false) {
            return null;
        }
        $documentType['document_type_id'] = $this->encryption->encrypt($documentType['document_type_id']);
        return $documentType;
    }

    public function createDocumentType($document_type_code, $dt_year, $dt_from, $dt_to, $sequence)
    {
        $query = 'INSERT INTO cm_sap.tb_document_types (document_type_code, dt_year, dt_from, dt_to, sequence, created_at, updated_at, is_deleted) 
                  VALUES ($1, $2, $3, $4, $5, NOW(), NOW(), false) RETURNING document_type_id';
        $result = pg_prepare($this->connection, "create_document_type", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for creating document type.');
        }
        $result = pg_execute($this->connection, "create_document_type", array($document_type_code, $dt_year, $dt_from, $dt_to, $sequence));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for creating document type.');
        }
        $documentTypeId = pg_fetch_result($result, 0, 0);
        return $this->encryption->encrypt($documentTypeId);
    }

    public function updateDocumentType($encryptedDocumentTypeId, $document_type_code, $dt_year, $dt_from, $dt_to)
    {
        $documentTypeId = $this->encryption->decrypt($encryptedDocumentTypeId);
        $query = 'UPDATE cm_sap.tb_document_types 
                  SET document_type_code = $2, dt_year = $3, dt_from = $4, dt_to = $5, updated_at = NOW() 
                  WHERE document_type_id = $1 AND is_deleted = false';
        $result = pg_prepare($this->connection, "update_document_type", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for updating document type.');
        }
        $result = pg_execute($this->connection, "update_document_type", array($documentTypeId, $document_type_code, $dt_year, $dt_from, $dt_to));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for updating document type.');
        }
        return pg_affected_rows($result);
    }

    public function deleteDocumentType($encryptedDocumentTypeId)
    {
        $documentTypeId = $this->encryption->decrypt($encryptedDocumentTypeId);
        $query = 'UPDATE cm_sap.tb_document_types SET is_deleted = true, updated_at = NOW() 
                  WHERE document_type_id = $1';
        $result = pg_prepare($this->connection, "delete_document_type", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for deleting document type.');
        }
        $result = pg_execute($this->connection, "delete_document_type", array($documentTypeId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for deleting document type.');
        }
        return pg_affected_rows($result);
    }

    public function incrementSequence($encryptedDocumentTypeId)
    {
        $documentTypeId = $this->encryption->decrypt($encryptedDocumentTypeId);
        $query = 'UPDATE cm_sap.tb_document_types 
                  SET sequence = sequence::int + 1, updated_at = NOW() 
                  WHERE document_type_id = $1 AND is_deleted = false RETURNING sequence';
        $result = pg_prepare($this->connection, "increment_sequence", $query);
        if (!$result) {
            throw new Exception('Failed to prepare SQL query for incrementing sequence.');
        }
        $result = pg_execute($this->connection, "increment_sequence", array($documentTypeId));
        if (!$result) {
            throw new Exception('Failed to execute SQL query for incrementing sequence.');
        }
        $newSequence = pg_fetch_result($result, 0, 0);
        return $newSequence;
    }
}
