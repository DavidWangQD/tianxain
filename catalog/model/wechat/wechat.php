<?php
class ModelWechatWechat extends Model {
	public function getToken() {

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "token LIMIT 1");

        return $query->row;

	}

    public function insertToken($timestamp, $token) {

        $result = $this->db->query("INSERT INTO " . DB_PREFIX . "token SET timestamp = '" . $timestamp . "', token = '" . $token . "'");

        return $result;

    }

    public function updateToken($id, $timestamp, $token) {

        $result = $this->db->query("UPDATE " . DB_PREFIX . "token SET timestamp = '" . $timestamp . "', token = '" . $token . "' WHERE id = '" . $id . "'");

        return $result;

    }

}
?>