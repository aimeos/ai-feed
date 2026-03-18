# ai-feed

Product feed exports for third-party platforms like Google Merchant Center and Idealo.

## Installation

```bash
composer require aimeos/ai-feed
```

Requires PHP 8.0.11+, `aimeos/aimeos-core`, `aimeos/ai-admin-jqadm`, `aimeos/ai-admin-graphql`, and `aimeos/ai-controller-jobs` (all 2025.10.*).

## Usage

### Admin Panel

The extension adds a **Feed** panel to the admin backend (shortcut key: **F**). From there you can create and manage feed configurations.

Each feed item has the following properties:

| Property | Description |
|---|---|
| **Label** | Name of the feed (used in the export file name) |
| **Type** | Export target (`google` or `idealo`) |
| **Language** | ISO language code for the export locale (e.g. `en`, `de`) |
| **Currency** | ISO currency code for prices (e.g. `EUR`, `USD`) |
| **Stock** | When enabled, only in-stock products are exported |
| **Status** | Enable/disable the feed |

### Filtering Products

Each feed can include or exclude products by category or individual product:

- **Include categories** — Only export products from these categories
- **Exclude categories** — Skip products belonging to these categories
- **Include products** — Always export these specific products
- **Exclude products** — Never export these specific products

### Attribute Mapping

The feed configuration supports an attribute mapping via the `config.attributes` key. This maps feed-specific column names to your product attribute or property types. The available mapping keys depend on the feed type (see sections below).

### Running Exports

Execute the export job controllers via CLI or scheduler:

- **Google:** `product/export/google`
- **Idealo:** `product/export/idealo`

Exported files are written to the `fs-export` filesystem using the feed label as file name (default: `<label>.csv`).

## Google Merchant Feed

Generates a CSV file compatible with Google Merchant Center.

### Exported Columns

`id`, `title`, `description`, `link`, `image_link`, `additional_image_link`, `availability`, `availability_date`, `cost_of_goods_sold`, `expiration_date`, `price`, `sale_price`, `sale_price_effective_date`, `google_product_category`, `product_type`, `brand`, `gtin`, `mpn`, `identifier_exists`, `condition`, `adult`, `multipack`, `is_bundle`, `certification`, `energy_efficiency_class`, `age_group`, `color`, `gender`, `material`, `pattern`, `size`, `size_type`, `size_system`, `item_group_id`, `product_length`, `product_width`, `product_height`, `product_weight`, `product_highlight`

### Attribute Type Mapping

The default attribute/property types used to populate Google columns:

| Google Column | Default Type |
|---|---|
| `gtin` | `gtin` |
| `mpn` | `mpn` |
| `material` | `material` |
| `pattern` | `pattern` |
| `color` | `colour` |
| `size` | `size` |
| `size_type` | `size_type` |
| `size_system` | `size_system` |
| `gender` | `gender` |
| `adult` | `adult` |
| `age_group` | `age_group` |
| `condition` | `condition` |
| `certification` | `certification` |
| `product_length` | `product_length` |
| `product_width` | `product_width` |
| `product_height` | `product_height` |
| `product_weight` | `product_weight` |

These can be customized via:

```php
'controller' => [
    'jobs' => [
        'product' => [
            'export' => [
                'google' => [
                    'types' => [
                        'gtin' => 'my-gtin-type',
                        'colour' => 'my-color-type',
                        // ...
                    ],
                ],
            ],
        ],
    ],
],
```

### Google-Specific Notes

- **google_product_category**: Set the `google-merchant` config key on a catalog item to map to a Google product category ID
- **brand**: Resolved from the product's supplier relation
- **item_group_id**: Automatically set to the parent product code for selection (variant) products
- **is_bundle**: Automatically set to `yes` for bundle-type products
- **Delivery costs**: Calculated from available delivery service providers

### Google Configuration Options

| Option | Default | Description |
|---|---|---|
| `controller/jobs/product/export/google/domains` | `['attribute', 'catalog', 'media', 'price', 'product', 'text']` | Associated domains fetched during export |
| `controller/jobs/product/export/google/filename` | `%s.csv` | File name template (`%s` = feed label) |
| `controller/jobs/product/export/google/max-items` | `1000` | Maximum products fetched per batch |
| `controller/jobs/product/export/google/template-header` | `product/export/google/items-header-standard` | CSV header template path |
| `controller/jobs/product/export/google/template-items` | `product/export/google/items-body-standard` | CSV body template path |

## Idealo Feed

Generates a CSV file compatible with the Idealo product import.

### Exported Columns

`sku`, `brand`, `title`, `categoryPath`, `url`, `hans`, `description`, `imageUrls`, `price`, `formerPrice`, `delivery`, `eans`, `size`, `colour`, `gender`, `material`, `eec_efficiencyClass`, `eec_labelUrl`, `eec_dataSheetUrl`, `eec_version`

### Attribute Type Mapping

The default attribute/property types used to populate Idealo columns:

| Idealo Column | Default Type |
|---|---|
| `hans` | `hans` |
| `eans` | `eans` |
| `material` | `material` |
| `colour` | `colour` |
| `size` | `size` |
| `gender` | `gender` |
| `delivery` | `delivery` |
| `eec_efficiencyClass` | `eec_efficiencyClass` |
| `eec_labelUrl` | `eec_labelUrl` |
| `eec_dataSheetUrl` | `eec_dataSheetUrl` |
| `eec_version` | `eec_version` |

These can be customized via:

```php
'controller' => [
    'jobs' => [
        'product' => [
            'export' => [
                'idealo' => [
                    'types' => [
                        'eans' => 'my-ean-type',
                        'colour' => 'my-color-type',
                        // ...
                    ],
                ],
            ],
        ],
    ],
],
```

### Idealo-Specific Notes

- **categoryPath**: Set the `idealo` config key on a catalog item to override the category name
- **brand**: Resolved from the product's supplier relation
- **Delivery costs**: Calculated from available delivery service providers

### Idealo Configuration Options

| Option | Default | Description |
|---|---|---|
| `controller/jobs/product/export/idealo/domains` | `['attribute', 'catalog', 'media', 'price', 'product', 'text']` | Associated domains fetched during export |
| `controller/jobs/product/export/idealo/filename` | `%s.csv` | File name template (`%s` = feed label) |
| `controller/jobs/product/export/idealo/max-items` | `1000` | Maximum products fetched per batch |
| `controller/jobs/product/export/idealo/template-header` | `product/export/idealo/items-header-standard` | CSV header template path |
| `controller/jobs/product/export/idealo/template-items` | `product/export/idealo/items-body-standard` | CSV body template path |

## Admin Access Control

By default, only users in the `admin` and `super` groups can access feed management:

```php
'admin' => [
    'graphql' => [
        'resource' => [
            'feed' => [
                'delete' => ['admin', 'super'],
                'save' => ['admin', 'super'],
                'get' => ['admin', 'super'],
            ],
        ],
    ],
    'jqadm' => [
        'resource' => [
            'feed' => [
                'groups' => ['admin', 'super'],
            ],
        ],
    ],
],
```

## License

The `ai-feed` extension is licensed under the terms of the [LGPLv3](https://opensource.org/licenses/LGPL-3.0).
