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

    public function getProducts(array $categories, $start, $limit)
    {
        $query = $this->db->query("SELECT p.product_id, pd.name, p.model, p.image, p.price"
            . $this->productsFrom($categories) . "
            ORDER BY pd.name, p.product_id
            LIMIT " . (int)$start . ", " . (int)$limit);

        return $query->rows;
    }

    public function getTotalProducts(array $categories)
    {
        return (int)$this->db->query("SELECT COUNT(*) AS total" . $this->productsFrom($categories))->row['total'];
    }

    // Скидки товаров для группы покупателей по умолчанию: [product_id => [количество => процент от цены]].
    public function getDiscounts(array $product_ids)
    {
        $discounts = array_fill_keys($product_ids, []);

        if (!$product_ids) {
            return $discounts;
        }

        $query = $this->db->query("SELECT d.product_id, d.quantity, ROUND(100 - d.price * 100 / p.price, 2) AS percent
            FROM `" . DB_PREFIX . "product_discount` d
            LEFT JOIN `" . DB_PREFIX . "product` p ON (d.product_id = p.product_id)
            WHERE d.product_id IN (" . implode(',', array_map('intval', $product_ids)) . ")
                AND d.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'
                AND p.price > 0
            ORDER BY d.product_id, d.quantity, d.priority");

        foreach ($query->rows as $row) {
            if (!isset($discounts[$row['product_id']][$row['quantity']])) {
                $discounts[$row['product_id']][$row['quantity']] = (float)$row['percent'];
            }
        }

        return $discounts;
    }

    private function productsFrom(array $categories)
    {
        $categories = implode(',', array_map('intval', $categories)) ?: '0';

        return " FROM `" . DB_PREFIX . "product` p
            LEFT JOIN `" . DB_PREFIX . "product_description` pd ON (p.product_id = pd.product_id)
            WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                AND p.product_id IN (SELECT product_id FROM `" . DB_PREFIX . "product_to_category` WHERE category_id IN (" . $categories . "))";
    }
}
