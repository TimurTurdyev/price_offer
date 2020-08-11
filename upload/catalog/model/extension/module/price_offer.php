<?php

class ModelExtensionModulePriceOffer extends Model {
    public function products($data) {
        $sql = "SELECT p.*, pd.name, ";
        $sql .= " @percent := (p.price / 100) as base_percent,
                    GROUP_CONCAT(
                        100 - d.price / @percent
                        ORDER BY 
                            d.quantity ASC SEPARATOR '|'
                    ) AS coefficient,
                    (
                    SELECT GROUP_CONCAT(
                        od.name, ': ', ovd.name, '=', 
                        CASE
                            WHEN pov.price_prefix = '+'
                                THEN (p.price + pov.price)
                            WHEN pov.price_prefix = '='
                                THEN pov.price
                            ELSE pov.price
                        END
                        ORDER BY 
                            pov.price ASC SEPARATOR '|'
                    )
                    FROM " . DB_PREFIX . "product_option_value pov
                    LEFT JOIN " . DB_PREFIX . "option_description od ON (pov.option_id = od.option_id)
                    LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (pov.option_value_id = ovd.option_value_id)
                    WHERE pov.product_id = p.product_id
                 ) as options";
        $sql .= " FROM " . DB_PREFIX . "product p";
        $sql .= " LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";
        $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
        $sql .= " LEFT JOIN " . DB_PREFIX . "product_discount d ON p.product_id = d.product_id";
        $sql .= " WHERE p.status = 1 AND p.jan != 'not_export' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $categories = [];

        if ($data['categories']) {
            foreach ($data['categories'] as $category) {
                if (is_numeric($category)) {
                    $categories[] = "p2c.category_id = '" . (int)$category . "'";
                }
            }
        }

        if (count($categories)) {
            $sql .= " AND (" . implode(' OR ', $categories) . ')';
        }

        $sql .= " GROUP BY p.product_id";
        $sql .= " ORDER BY p2c.category_id ASC, p.product_id ASC";
        // print_r($sql);die();
        $query = $this->db->query($sql);
        return $query->rows;
    }
}