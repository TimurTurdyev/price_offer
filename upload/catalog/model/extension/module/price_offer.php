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
            LEFT JOIN `" . DB_PREFIX . "price_offer_exclude` e ON (p.product_id = e.product_id)
            WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                AND p.status = '1'
                AND e.product_id IS NULL
                AND p.product_id IN (SELECT product_id FROM `" . DB_PREFIX . "product_to_category` WHERE category_id IN (" . $categories . "))
            ORDER BY (SELECT MIN(p2c.category_id) FROM `" . DB_PREFIX . "product_to_category` p2c
                WHERE p2c.product_id = p.product_id AND p2c.category_id IN (" . $categories . ")), p.sort_order, pd.name");

        $products = [];

        foreach ($query->rows as $row) {
            $row['discounts'] = [];
            $row['options'] = [];
            $products[$row['product_id']] = $row;
        }

        if ($products) {
            $this->addDiscounts($products);
            $this->addOptions($products);
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

    private function addOptions(array &$products)
    {
        $language_id = (int)$this->config->get('config_language_id');

        $query = $this->db->query("SELECT pov.product_id, od.name AS `option`, ovd.name AS value, pov.price, pov.price_prefix
            FROM `" . DB_PREFIX . "product_option_value` pov
            LEFT JOIN `" . DB_PREFIX . "option_description` od ON (pov.option_id = od.option_id AND od.language_id = '" . $language_id . "')
            LEFT JOIN `" . DB_PREFIX . "option_value_description` ovd ON (pov.option_value_id = ovd.option_value_id AND ovd.language_id = '" . $language_id . "')
            WHERE pov.product_id IN (" . implode(',', array_keys($products)) . ")
            ORDER BY pov.product_id, od.name, pov.price");

        foreach ($query->rows as $row) {
            $price = $products[$row['product_id']]['price'];

            if ($row['price_prefix'] == '+') {
                $price += $row['price'];
            } elseif ($row['price_prefix'] == '-') {
                $price -= $row['price'];
            } else {
                $price = $row['price'];
            }

            $products[$row['product_id']]['options'][] = [
                'name'  => $row['option'] . ': ' . $row['value'],
                'price' => $price,
            ];
        }
    }
}
