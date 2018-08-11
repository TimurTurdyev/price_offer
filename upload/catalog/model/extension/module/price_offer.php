<?php
class ModelExtensionModulePriceOffer extends Model
{
    public function getProducts(array $categories)
    {
        $categories = implode(',', array_map('intval', $categories));

        if (!$categories) {
            return [];
        }

        $query = $this->db->query("SELECT p.product_id, pd.name, p.model, p.image, p.price, p.minimum
            FROM `" . DB_PREFIX . "product` p
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id)
            WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                AND p.status = '1'
                AND p.product_id IN (SELECT product_id FROM `" . DB_PREFIX . "product_to_category` WHERE category_id IN (" . $categories . "))
            ORDER BY (SELECT MIN(p2c.category_id) FROM `" . DB_PREFIX . "product_to_category` p2c
                WHERE p2c.product_id = p.product_id AND p2c.category_id IN (" . $categories . ")), p.sort_order, pd.name");

        return $query->rows;
    }
}
