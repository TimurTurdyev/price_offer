<?php
class ModelExtensionModulePriceOffer extends Model
{
    public function getCategories()
    {
        $query = $this->db->query("SELECT cp.category_id,
                GROUP_CONCAT(cd.name ORDER BY cp.level SEPARATOR ' &gt; ') AS name,
                (SELECT COUNT(*) FROM `" . DB_PREFIX . "product_to_category` p2c WHERE p2c.category_id = cp.category_id) AS products
            FROM `" . DB_PREFIX . "category_path` cp
            LEFT JOIN `" . DB_PREFIX . "category_description` cd ON (cp.path_id = cd.category_id)
            WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "'
            GROUP BY cp.category_id
            ORDER BY name");

        return $query->rows;
    }
}
