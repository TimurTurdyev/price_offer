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

        $products = [];

        foreach ($query->rows as $row) {
            $row['discounts'] = [];
            $products[$row['product_id']] = $row;
        }

        if ($products) {
            $this->addDiscounts($products);
        }

        return array_values($products);
    }

    // Скидки за количество из карточки товара в процентах от цены, по возрастанию количества.
    private function addDiscounts(array &$products)
    {
        $query = $this->db->query("SELECT product_id, quantity, price FROM `" . DB_PREFIX . "product_discount`
            WHERE product_id IN (" . implode(',', array_keys($products)) . ")
                AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'
            ORDER BY product_id, quantity, priority");

        $quantities = [];

        foreach ($query->rows as $row) {
            $product = &$products[$row['product_id']];

            if ($product['price'] > 0 && !isset($quantities[$row['product_id']][$row['quantity']])) {
                $quantities[$row['product_id']][$row['quantity']] = true;
                $product['discounts'][] = round(100 - $row['price'] * 100 / $product['price'], 2);
            }

            unset($product);
        }
    }
}
