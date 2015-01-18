<?php
class ModelProductCategory extends Model {

	public function getProducts() {

        $today = date('Y-m-d');

        $sql = <<<HTML

            SELECT
                tcd.name AS category_name,
                tpd.name AS product_name,
                tpd.description AS product_description,
                tp.image,
                tp.price,
                tpd.name,
                tp.product_id,
                tc.category_id,
                tp.quantity,
                twcd.title AS weight_class,
                (SELECT
                        price
                    FROM
                        tx_product_special AS tps
                    WHERE
                        tp.product_id = tps.product_id
                            AND ((tps.date_start <= "{$today}"
                            AND tps.date_end >= "{$today}")
                            OR (tps.date_start = '0000-00-00'
                            AND tps.date_end >= '0000-00-00'))
                    ORDER BY tps.priority ASC
                    LIMIT 1) AS special
            FROM
                tx_category AS tc
                    LEFT JOIN
                tx_category_description AS tcd ON tc.category_id = tcd.category_id
                    LEFT JOIN
                tx_product_to_category AS tp2c ON tc.category_id = tp2c.category_id
                    LEFT JOIN
                tx_product AS tp ON tp2c.product_id = tp.product_id
                    LEFT JOIN
                tx_product_description AS tpd ON tp.product_id = tpd.product_id
                    LEFT JOIN
                tx_weight_class_description AS twcd ON tp.weight_class_id = twcd.weight_class_id
            WHERE
                tcd.language_id = 2
                    AND tpd.language_id = 2
                    AND twcd.language_id = 2
                    AND tp.status = 1
            ORDER BY tc.sort_order ASC;

HTML;

		return $this->db->query($sql)->rows;
	}

}
?>