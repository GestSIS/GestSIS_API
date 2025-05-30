<?php

namespace App\Domaine\Business\Materiel;

use App\Exceptions\InternalException;
use App\Utils\FPDF;

/**
 * Model for printing PDFs
 * Available public methods
 * @static printBatteries($locationId)
 * @static printInventory($locationId)
 * @static printPipes()
 */
class PrintBusiness
{
  /**
   * Print control sheets
   * @param integer $controlId ID of the control to print
   * @return FPDF PDF file to print
   */
  public static function printControl($controlId)
  {

    // Create PDF, landscape
    $pdf = self::createPdf(false, true);

    // Retrieve list of control groups
    $groups = ControlModel::getControlFull($controlId)['groups'];

    // Print all control groups in sequence
    foreach ($groups as $group) {
      self::printControlGroupContent($pdf, $group['id']);
    }

    // Return the PDF
    return $pdf;
  }

  /**
   * Print control group sheet
   * @param integer $groupId ID of the control group to print
   * @return FPDF PDF file to print
   */
  public static function printControlGroup($groupId)
  {

    // Create PDF, landscape
    $pdf = self::createPdf(false, true);

    // Print single control group
    self::printControlGroupContent($pdf, $groupId);

    // Return the PDF
    return $pdf;
  }

  /**
   * Print list of batteries
   * @param integer|string $locationId ID of the location for which to print, can be "all"
   * @return FPDF PDF file to print
   */
  public static function printBatteries($locationId)
  {

    // Gather data to print
    $locations = LocationModel::getLocationForBatteries($locationId);

    // Create PDF
    $pdf = self::createPdf(false);

    // Initialize row trackers
    $maxRows = 50; // Number of rows that fit on a single page
    $currentRow = 0; // ID of the current row, when reached 50, reinitialized to 0

    // Helper to create a new page and add header to it
    $goToNextPage = function ($withHeader = true) use (&$pdf, &$currentRow) {
      // Next page
      $pdf->AddPage();

      // Reinitialize rows
      $currentRow = 0;

      // Main title of the page
      $pdf->SetFont('Tahoma', 'B', 14);
      $pdf->SetXY(20, 12);
      $pdf->Cell(170, 10, utf8_decode("Liste des batteries"), 0, 0, "C");

      // Header row of the page
      if ($withHeader) {
        $pdf->SetFont('Tahoma', 'B', 10);
        $pdf->SetXY(20, 30);
        $pdf->Cell(60, 5, utf8_decode("Emplacement"), 0, 0, "L");
        $pdf->Cell(55, 5, utf8_decode("Matériel"), 0, 0, "L");
        $pdf->Cell(25, 5, utf8_decode("P/pièce"), 0, 0, "L");
        $pdf->Cell(15, 5, utf8_decode("Pces"), 0, 0, "R");
        $pdf->Cell(15, 5, utf8_decode("Total"), 0, 0, "R");
      } else {
        $pdf->SetFont('Tahoma', 'I', 12);
        $pdf->SetXY(20, 22);
        $pdf->Cell(170, 8, utf8_decode("Quantités pour achats"), 0, 0, "C");
      }

      // Default font for rows
      $pdf->SetFont('Tahoma', '', 10);
    };

    // Initialize first page
    $goToNextPage();

    // Go through locations and print, aggregate sums at same time
    $summary = [];
    foreach ($locations as $location) {

      // Go through compartments of the given location
      $locationFirst = true;
      foreach ($location['compartments'] as $compartment) {

        // Determine number of rows required to print the compartment
        $compartmentRows = ($locationFirst ? 1 : 0) + Arrays::size($compartment['products']);
        $remainingRows = $maxRows - $currentRow;

        // Next page if required
        if ($compartmentRows <= 5 && $compartmentRows > $remainingRows) {
          $goToNextPage();
        }

        // If first compartment, print location first
        if ($locationFirst) {
          $pdf->SetXY(20, 35 + $currentRow * 5 + 0.5);
          self::printStickers($pdf, $location['location']['stickers'], 4);
          $pdf->Line(20, 35 + $currentRow * 5, 190, 35 + $currentRow * 5);
          $currentRow += 1;
          if ($currentRow >= $maxRows) {
            $goToNextPage();
          }
        }

        // Print products
        $compartmentFirst = true;
        foreach ($compartment['products'] as $product) {

          // Print compartment if first product
          if ($compartmentFirst) {
            $pdf->SetXY(20 + 5, 35 + $currentRow * 5);
            $pdf->Cell(55, 5, utf8_decode($compartment['compartment']), 0, 0, "L");
          } else {
            $pdf->SetXY(20 + 60, 35 + $currentRow * 5);
          }

          // Compute total required batteries and add to summary
          $total = $product['count'] * $product['battery']['count'];
          if (!Arrays::has($summary, $product['battery']['type'])) {
            $summary[$product['battery']['type']] = 0;
          }
          $summary[$product['battery']['type']] += $total;

          // Print row
          $pdf->Cell(55, 5, utf8_decode($product['product']['name']), 0, 0, "L");
          $pdf->Cell(25, 5, utf8_decode($product['battery']['count'] . "x " . $product['battery']['type']), 0, 0, "L");
          $pdf->Cell(15, 5, utf8_decode($product['count']), 0, 0, "R");
          $pdf->Cell(15, 5, utf8_decode($total), 0, 0, "R");

          // Next row
          $currentRow += 1;
          if ($currentRow >= $maxRows) {
            $goToNextPage();
          }

          // No longer first product of the compartment
          $compartmentFirst = false;
        }

        // No longer first compartment of a location
        $locationFirst = false;
      }
    }

    // Print summary
    $goToNextPage(false);
    $pdf->SetFont('Tahoma', 'B', 10);
    $pdf->SetXY(80, 35);
    $pdf->Cell(35, 5, utf8_decode("Type"), 0, 0, "L");
    $pdf->Cell(15, 5, utf8_decode("Qté"), 0, 0, "R");
    $pdf->SetFont('Tahoma', '', 10);
    $currentRow = 0;
    foreach ($summary as $type => $count) {
      $pdf->SetXY(80, 40 + $currentRow * 5);
      $pdf->Cell(35, 5, utf8_decode($type), 0, 0, "L");
      $pdf->Cell(15, 5, utf8_decode($count), 0, 0, "R");
      $pdf->Line(80, 40 + $currentRow * 5, 130, 40 + $currentRow * 5);
      $currentRow += 1;
    }

    // Return the PDF
    return $pdf;
  }

  /**
   * Print full inventory
   * @return FPDF PDF file to print
   */
  public static function printFullInventory()
  {
    // Retrieve full inventory
    $query = <<<EOF
      SELECT
        CA.id AS category_id,
        CA.name AS category_name,
        CO.id AS color_id,
        CO.name AS color_name,
        CO.foreground AS color_foreground,
        CO.background AS color_background,
        P.id AS product_id,
        P.name AS product_name,
        COUNT(I.id) AS count
      FROM item I
      INNER JOIN product P ON I.product_id = P.id
      INNER JOIN category CA ON P.category_id = CA.id
      INNER JOIN color CO ON CA.color_id = CO.id
      WHERE I.deleted IS NULL
      GROUP BY P.id
      ORDER BY CA.`order` ASC, P.`order` ASC
EOF;
    $products = self::db()->select($query);

    // Prepare data for printing
    $categories = [];
    foreach ($products as $product) {
      // Create category sub-array if not exist
      if (!Arrays::has($categories, $product->category_id)) {
        $categories[$product->category_id] = [
          'category' => [
            'id' => $product->category_id,
            'name' => $product->category_name,
            'color' => [
              'id' => $product->color_id,
              'name' => $product->color_name,
              'foreground' => $product->color_foreground,
              'background' => $product->color_background
            ]
          ],
          'products' => []
        ];
      }

      // Add product to array
      $categories[$product->category_id]['products'][] = [
        'id' => $product->product_id,
        'name' => $product->product_name,
        'count' => $product->count
      ];
    }

    // Create PDF
    $pdf = self::createPdf();

    // Tracker for pages
    $page = 1;

    // Helper to print page header
    $printHeader = function () use (&$pdf, &$location, &$page) {
      // Print main "Inventaire" title
      $pdf->SetFont('Tahoma', 'B', 16);
      $pdf->SetXY(20, 12);
      $pdf->Cell(170, 6, utf8_decode('Inventaire général'), 0, 0, "C");

      // Print sub titles
      $pdf->SetFont('Tahoma', '', 10);
      $pdf->SetXY(170, 24);
      $pdf->Cell(20, 5, utf8_decode('Page ' . $page), 0, 0, "R");
      $page += 1;
    };
    $printHeader();

    // Trackers for rows
    $row = 0;
    $maxRows = 50;

    // Helper for printing
    $nextPage = function () use (&$pdf, &$row, &$printHeader) {
      $pdf->AddPage();
      $row = 0;
      $printHeader();
    };
    $nextRow = function () use (&$pdf, &$row, &$nextPage) {
      $baseY = 35;
      $pdf->SetXY(20, $baseY + $row * 5);
      $row += 1;
    };

    // Print data
    foreach ($categories as $category) {
      // Determine remaining rows
      $remainingRows = $maxRows - $row;

      // Determine if must go to new page
      if ($remainingRows <= 5 && count($category['products']) > $remainingRows) {
        $row = $maxRows;
      }

      // Print category header
      if ($row >= $maxRows - 1) {
        $nextPage();
      }
      $nextRow();
      list($fr, $fg, $fb) = sscanf($category['category']['color']['foreground'], "%02x%02x%02x");
      list($br, $bg, $bb) = sscanf($category['category']['color']['background'], "%02x%02x%02x");
      $pdf->SetTextColor($fr, $fg, $fb);
      $pdf->SetFillColor($br, $bg, $bb);
      $pdf->SetFont('Tahoma', 'B', 10);
      $pdf->Cell(170, 5, utf8_decode($category['category']['name']), 1, 0, "L", true);
      $pdf->SetFont('Tahoma', '', 10);
      $pdf->SetTextColor(0);
      $pdf->SetFillColor(255);

      // Print products
      foreach ($category['products'] as $product) {
        // Break page if needed
        if ($row >= $maxRows) {
          $nextPage();
        }
        $nextRow();

        // Print product name
        $pdf->SetX(20);
        $pdf->Cell(75, 5, utf8_decode($product['name']), 0, 0, "L");
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());

        // Print product count
        $pdf->SetX(170);
        $pdf->Cell(20, 5, utf8_decode($product['count']), 0, 0, "R");
      }
    }

    return $pdf;
  }

  /**
   * Print inventory for a location
   * @param integer $locationId ID of the location for which to print
   * @return FPDF PDF file to print
   */
  public static function printInventory($locationId)
  {

    // Gather data to print
    $location = LocationModel::getLocationFull($locationId);

    // Helper to remove stickers that are already part of a parent
    $removeParentStickers = function ($stickers, $parentStickers) {
      $childStickers = [];
      foreach ($stickers as $idx => $sticker) {
        if (
          !Arrays::has($parentStickers, $idx)
          ||
          $parentStickers[$idx]['name'] !== $sticker['name']
          ||
          $parentStickers[$idx]['color']['foreground'] !== $sticker['color']['foreground']
          ||
          $parentStickers[$idx]['color']['background'] !== $sticker['color']['background']
        ) {
          $childStickers[] = $sticker;
        }
      }
      return $childStickers;
    };

    // Prepare rows to print
    // Map each category to simplify products
    $categories = Arrays::each(
      $location['categories'],
      function ($category) use (&$location, &$removeParentStickers) {

        // Build products array for the category
        $products = [];
        foreach ($category['products'] as $product) {
          $productKey = 'p' . $product['product']['id'];

          // If product not already listed, add
          if (!Arrays::has($products, $productKey)) {
            $products[$productKey] = [
              'product' => $product['product'],
              'rows' => []
            ];
          }

          // Add rows according to compartments
          foreach ($product['items'] as $item) {
            $locationCompartmentKey = 'l' . $product['location']['id'] . 'c' . $item['compartment'];

            // If item has been deleted, do not consider
            if ($item['deleted']) {
              continue;
            }

            // If location/compartment couple not already listed, add
            if (!Arrays::has($products[$productKey]['rows'], $locationCompartmentKey)) {
              $products[$productKey]['rows'][$locationCompartmentKey] = [
                'location' => [
                  'id' => $product['location']['id'],
                  'stickers' => $removeParentStickers(
                    $product['location']['stickers'],
                    $location['stickers']
                  )
                ],
                'compartment' => $item['compartment'],
                'count' => 0
              ];
            }

            // Increment count
            $products[$productKey]['rows'][$locationCompartmentKey]['count'] += 1;
          }
        }
        return [
          'category' => $category['category'],
          'products' => array_values(
            Arrays::each(
              $products,
              function ($product) {
                return [
                  'product' => $product['product'],
                  'rows' => array_values($product['rows'])
                ];
              }
            )
          ),
          'count' => Arrays::sum(
            array_values(
              Arrays::each(
                $products,
                function ($product) {
                  return Arrays::size($product['rows']);
                }
              )
            )
          ) + 1
        ];
      }
    );

    // Create PDF
    $pdf = self::createPdf();

    // Tracker for pages
    $page = 1;

    // Helper to print page header
    $printHeader = function () use (&$pdf, &$location, &$page) {
      // Print main "Inventaire" title
      $pdf->SetFont('Tahoma', 'B', 16);
      $pdf->SetXY(20, 12);
      $pdf->Cell(170, 6, utf8_decode('Inventaire'), 0, 0, "C");

      // Print sub titles
      $pdf->SetFont('Tahoma', '', 10);
      $pdf->SetXY(20, 24);
      $pdf->Cell(26, 5, utf8_decode('Emplacement :'), 0, 0, "L");
      $pdf->Cell(45, 5, utf8_decode($location['name']), 0, 0, "L");
      $pdf->Cell(23, 5, utf8_decode('Etiquettage :'), 0, 0, "L");
      self::printStickers($pdf, $location['stickers']);
      $pdf->SetXY(170, 24);
      $pdf->Cell(20, 5, utf8_decode('Page ' . $page), 0, 0, "R");
      $page += 1;
    };
    $printHeader();

    // Trackers for rows
    $row = 0;
    $maxRows = 50;

    // Helper for printing
    $nextPage = function () use (&$pdf, &$row, &$printHeader) {
      $pdf->AddPage();
      $row = 0;
      $printHeader();
    };
    $nextRow = function () use (&$pdf, &$row, &$nextPage) {
      $baseY = 35;
      $pdf->SetXY(20, $baseY + $row * 5);
      $row += 1;
    };

    // Print categories
    foreach ($categories as $category) {
      // Determine remaining rows
      $remainingRows = $maxRows - $row;

      // Determine if must go to new page
      if ($remainingRows <= 5 && $category['count'] > $remainingRows) {
        $row = $maxRows;
      }

      // Print category header
      if ($row >= $maxRows - 1) {
        $nextPage();
      }
      $nextRow();
      list($fr, $fg, $fb) = sscanf($category['category']['color']['foreground'], "%02x%02x%02x");
      list($br, $bg, $bb) = sscanf($category['category']['color']['background'], "%02x%02x%02x");
      $pdf->SetTextColor($fr, $fg, $fb);
      $pdf->SetFillColor($br, $bg, $bb);
      $pdf->SetFont('Tahoma', 'B', 10);
      $pdf->Cell(170, 5, utf8_decode($category['category']['name']), 1, 0, "L", true);
      $pdf->SetFont('Tahoma', '', 10);
      $pdf->SetTextColor(0);
      $pdf->SetFillColor(255);

      // Print products
      foreach ($category['products'] as $product) {
        // Determine remaining rows
        $remainingRows = $maxRows - $row;

        // Go to new page if not enough place for the whole product
        if (
          Arrays::size($product['rows']) > $remainingRows
          &&
          Arrays::size($product['rows']) <= $maxRows
        ) {
          $nextPage();
        }

        // Print rows
        $first = true;
        foreach ($product['rows'] as $rowElement) {

          // Break page if needed
          if ($row >= $maxRows) {
            $nextPage();
          }
          $nextRow();

          // Print product name if first row
          if ($first) {
            $first = false;
            $pdf->SetX(20);
            $pdf->Cell(75, 5, utf8_decode($product['product']['name']), 0, 0, "L");
            $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
          }

          // Print location (if different) and compartment
          $pdf->SetX(95);
          if ($rowElement['location']['id'] !== $location['id']) {
            $pdf->SetY($pdf->GetY() + 0.5, false);
            self::printStickers($pdf, $rowElement['location']['stickers'], 4);
            $pdf->SetY($pdf->GetY() - 0.5, false);
          }
          $pdf->Cell(170 - $pdf->GetX(), 5, utf8_decode($rowElement['compartment']));

          // Print count
          $pdf->SetX(170);
          $pdf->Cell(20, 5, utf8_decode($rowElement['count']), 0, 0, "R");
        }
      }
    }

    // Update location with last printed date
    LocationModel::updateLastPrinted($locationId);

    // Return PDF file
    return $pdf;
  }

  /**
   * Print inventory to fill for a location and given inventory basic details
   * @param integer $locationId ID of the location for which to print
   * @param array $inventory Details of inventory to print
   * @return FPDF PDF file to print
   */
  public static function printInventoryToFill($locationId, $inventory)
  {

    // Gather data to print
    $locationDetails = LocationModel::getLocationBasic($locationId);
    $inventoryContent = ItemModel::getItemsForInventory($locationId);

    // Grid constants
    $GRID_TOP = 25; // number of pixels from top of page before first row of grid
    $GRID_LEFT = 20; // Left margin
    $GRID_ROW_HEIGHT = 5; // number of pixels defining height of a single grid row
    $GRID_ROW_COUNT = 34; // number of grid rows per page

    // Col size
    $GRID_COL_LOCATION = 80;
    $GRID_COL_CATEGORY = 65;
    $GRID_COL_PRODUCT = 72;
    $GRID_COL_NUMBER = 20;
    $GRID_COL_EXPECTED = 10;
    $GRID_COL_FOUND = 10;

    // Create PDF
    $pdf = self::createPdf(true, true);

    // Grid helpers
    $currentRow = -1;
    $printTableHeader = function () use (&$pdf, $GRID_COL_LOCATION, $GRID_COL_CATEGORY, $GRID_COL_PRODUCT, $GRID_COL_NUMBER, $GRID_COL_EXPECTED, $GRID_COL_FOUND) {
      $pdf->Cell($GRID_COL_LOCATION, 5, utf8_decode('Emplacement'), 0, 0, "L");
      $pdf->Cell($GRID_COL_CATEGORY, 5, utf8_decode('Catégorie'), 0, 0, "L");
      $pdf->Cell($GRID_COL_PRODUCT, 5, utf8_decode('Matériel'), 0, 0, "L");
      $pdf->Cell($GRID_COL_NUMBER, 5, utf8_decode('N°'), 0, 0, "L");
      $pdf->SetFont('Tahoma', '', 7);
      $pdf->Cell($GRID_COL_EXPECTED, 5, utf8_decode('Supposé'), 0, 0, "L");
      $pdf->Cell($GRID_COL_FOUND, 5, utf8_decode('Trouvé'), 0, 0, "L");
      $pdf->SetFont('Tahoma', '', 9);
    };
    $nextRow = function () use (&$pdf, &$currentRow, &$printTableHeader, $GRID_TOP, $GRID_LEFT, $GRID_ROW_HEIGHT, $GRID_ROW_COUNT) {
      $currentRow++;
      $newPage = false;
      // Go to next page if reached current page end
      if ($currentRow >= $GRID_ROW_COUNT) {
        $pdf->AddPage();
        $pdf->SetXY($GRID_LEFT, $GRID_TOP);
        $printTableHeader();
        $currentRow = 1;
        $newPage = true;
      }
      $pdf->SetXY($GRID_LEFT, $GRID_TOP + $currentRow * $GRID_ROW_HEIGHT);
      return $newPage;
    };

    // Print title
    $pdf->SetFont('Tahoma', 'B', 12);
    $pdf->SetXY($GRID_LEFT, 12);
    $pdf->Cell(257, 6, utf8_decode('Inventaire à remplir'), 0, 0, "C");

    // Print first page header
    $nextRow();
    $pdf->SetFont('Tahoma', '', 9);
    $pdf->Cell(30, 5, utf8_decode('Emplacement :'), 0, 0, "L");
    self::printStickers($pdf, $locationDetails['stickers']);
    if (strlen($inventory['title']) > 0) {
      $nextRow();
      $pdf->Cell(30, 5, utf8_decode('Titre :'), 0, 0, "L");
      $pdf->Cell(227, 5, utf8_decode($inventory['title']), 0, 0, "L");
    }
    if (!is_null($inventory['date'])) {
      $nextRow();
      $pdf->Cell(30, 5, utf8_decode('Date :'), 0, 0, "L");
      $pdf->Cell(227, 5, utf8_decode(date('d.m.Y', strtotime($inventory['date']))), 0, 0, "L");
    }
    if (strlen($inventory['people']) > 0) {
      $nextRow();
      $pdf->Cell(30, 5, utf8_decode('Participants :'), 0, 0, "L");
      $pdf->Cell(227, 5, utf8_decode($inventory['people']), 0, 0, "L");
    }
    if (strlen($inventory['remark']) > 0) {
      $nextRow();
      $pdf->Cell(30, 5, utf8_decode('Remarque :'), 0, 0, "L");
      $pdf->MultiCell(227, 5, utf8_decode($inventory['remark']), 0, "L");
      for ($i = 0; $i < substr_count($inventory['remark'], "\n"); $i++) {
        $nextRow();
      }
    }
    $nextRow();
    $nextRow();
    $printTableHeader();

    // Iterage over inventory locations and print
    foreach ($inventoryContent as $inventoryLocation) {

      // Iterate over categories of given location
      $locationFirst = true;
      foreach ($inventoryLocation['categories'] as $category) {

        // Iterate over items of given category
        $categoryFirst = true;
        foreach ($category['items'] as $item) {

          // Start row, if new page then force headers
          if ($nextRow()) {
            $locationFirst = true;
            $categoryFirst = true;
          }

          // Print location if needed
          if ($locationFirst) {
            self::printStickers($pdf, $inventoryLocation['location']['stickers']);
            $pdf->Cell($GRID_COL_LOCATION + $GRID_LEFT - $pdf->GetX(), 5, utf8_decode($inventoryLocation['compartment']), 0, 0, "L");
            $pdf->Line(20, $pdf->GetY(), 277, $pdf->GetY());
          } else {
            $pdf->Cell($GRID_COL_LOCATION, 5, '', 0, 0, "L");
          }

          // Print category if needed
          if ($categoryFirst) {
            list($fr, $fg, $fb) = sscanf($category['category']['color']['foreground'], "%02x%02x%02x");
            list($br, $bg, $bb) = sscanf($category['category']['color']['background'], "%02x%02x%02x");
            $pdf->SetTextColor($fr, $fg, $fb);
            $pdf->SetFillColor($br, $bg, $bb);
            $pdf->Cell($GRID_COL_CATEGORY, 5, utf8_decode($category['category']['name']), 1, 0, "L", true);
            $pdf->SetTextColor(0);
            $pdf->SetFillColor(255);
            $pdf->Line(20 + $GRID_COL_LOCATION, $pdf->GetY(), 277, $pdf->GetY());
          } else {
            $pdf->Cell($GRID_COL_CATEGORY, 5, '', 0, 0, "L");
          }

          // Print product name
          $pdf->Cell($GRID_COL_PRODUCT, 5, utf8_decode($item['product']['name']), 0, 0, "L");

          // Print item number if needed
          if (!is_null($item['product']['prefix']) && !is_null($item['item']['number'])) {
            $pdf->Cell($GRID_COL_NUMBER, 5, utf8_decode($item['product']['prefix'] . $item['item']['number']), 0, 0, "L");
          } else {
            $pdf->Cell($GRID_COL_NUMBER, 5, '', 0, 0, "L");
          }

          // Print expected amount
          $pdf->Cell($GRID_COL_EXPECTED, 5, utf8_decode($item['expected']), 0, 0, "R");

          // Print found cell
          $pdf->Cell($GRID_COL_FOUND, 5, '', 1, 0, "L");

          // Don't print headers for further items
          $locationFirst = false;
          $categoryFirst = false;
        }
      }
    }

    // Return PDF file
    return $pdf;
  }

  /**
   * Print list of products for a given owner
   * @param integer $ownerId ID of the owner for which to print products list
   * @return FPDF PDF file to print
   */
  public static function printOwner($ownerId)
  {

    // Gather data
    $owner = OwnerModel::getOwner($ownerId);
    $categories = ProductModel::listProductsByOwner($ownerId);

    // Grid constants
    $GRID_TOP = 35; // number of pixels from top of page before first row of grid
    $GRID_LEFT = 20; // Left margin
    $GRID_ROW_HEIGHT = 5; // number of pixels defining height of a single grid row
    $GRID_ROW_COUNT = 48; // number of grid rows per page

    // Create PDF
    $pdf = self::createPdf(false);

    // Grid helpers
    $currentRow = $GRID_ROW_COUNT;
    $nextRow = function () use (&$pdf, &$currentRow, $owner, $GRID_TOP, $GRID_LEFT, $GRID_ROW_HEIGHT, $GRID_ROW_COUNT) {
      $currentRow++;
      $newPage = false;
      // Go to next page if reached current page end
      if ($currentRow >= $GRID_ROW_COUNT) {
        $pdf->AddPage();
        $pdf->SetFont('Tahoma', 'B', 10);
        $pdf->SetXY(20, 20);
        $pdf->Cell(40, 5, utf8_decode('Groupe matériel :'), 0, 0, "L");
        $pdf->Cell(130, 5, utf8_decode($owner['name']), 0, 0, "L");
        $pdf->SetXY(20, 25);
        $pdf->Cell(40, 5, utf8_decode('Responsable :'), 0, 0, "L");
        $pdf->Cell(130, 5, utf8_decode($owner['manager']['name']), 0, 0, "L");
        $pdf->SetFont('Tahoma', '', 10);
        $currentRow = 0;
        $newPage = true;
      }
      $pdf->SetXY($GRID_LEFT, $GRID_TOP + $currentRow * $GRID_ROW_HEIGHT);
      return $newPage;
    };

    // Iterate over categories
    foreach ($categories as $category) {

      // Iterate over products of given category
      $categoryFirst = true;
      foreach ($category['products'] as $product) {

        // Start row, if new page then force headers
        if ($nextRow()) {
          $categoryFirst = true;
        }

        // Print category if required
        if ($categoryFirst) {
          if ($currentRow > $GRID_ROW_COUNT - 2) {
            while (!$nextRow())
              ;
          }
          list($fr, $fg, $fb) = sscanf($category['category']['color']['foreground'], "%02x%02x%02x");
          list($br, $bg, $bb) = sscanf($category['category']['color']['background'], "%02x%02x%02x");
          $pdf->SetFont('Tahoma', 'B', 10);
          $pdf->SetTextColor($fr, $fg, $fb);
          $pdf->SetFillColor($br, $bg, $bb);
          $pdf->Cell(170, 5, utf8_decode($category['category']['name']), 0, 0, "L", true);
          $pdf->SetTextColor(0);
          $pdf->SetFillColor(255);
          $pdf->SetFont('Tahoma', '', 10);
          $nextRow();
        }

        // Print product
        $pdf->SetX($pdf->GetX() + 5);
        $pdf->Cell(165, 5, utf8_decode($product['name']), 0, 0, "L");

        $categoryFirst = false;
      }
    }

    return $pdf;
  }

  /**
   * Print list of items for a given product
   * @param integer $productId ID of the product for which to print items list
   * @return FPDF PDF file to print
   */
  public static function printProduct($productId)
  {

    // Gather data
    $product = ProductModel::getProductForEditMinimal($productId);
    $items = ItemModel::getArticlesParMaterielType($productId);

    // Aggregate items by location and compartment
    $locations = array();
    foreach ($items as $item) {
      $locationKey = 'L' . $item['location']['id'];
      if (!array_key_exists($locationKey, $locations)) {
        $locations[$locationKey] = array(
          'location' => $item['location'],
          'compartments' => array()
        );
      }

      $compartmentKey = $item['compartment'];
      if (!array_key_exists($compartmentKey, $locations[$locationKey]['compartments'])) {
        $locations[$locationKey]['compartments'][$compartmentKey] = 0;
      }
      $locations[$locationKey]['compartments'][$compartmentKey] += 1;
    }
    usort($locations, function ($a, $b) {
      $mapToStr = function ($location) {
        return implode(
          ' ',
          array_map(
            function ($sticker) {
              return $sticker['name'];
            },
            $location['location']['stickers']
          )
        );
      };
      return strcmp($mapToStr($a), $mapToStr($b));
    });
    foreach ($locations as $location) {
      ksort($location['compartments']);
    }

    // Grid constants
    $GRID_TOP = 35; // number of pixels from top of page before first row of grid
    $GRID_LEFT = 20; // Left margin
    $GRID_ROW_HEIGHT = 7; // number of pixels defining height of a single grid row
    $GRID_ROW_COUNT = 34; // number of grid rows per page

    // Col size
    $GRID_COL_LOCATION = 95;
    $GRID_COL_COMPARTMENT = 60;
    $GRID_COL_COUNT = 15;

    // Create PDF
    $pdf = self::createPdf(false);

    // Grid helpers
    $currentRow = $GRID_ROW_COUNT;
    $nextRow = function () use (&$pdf, &$currentRow, $product, $GRID_TOP, $GRID_LEFT, $GRID_ROW_HEIGHT, $GRID_ROW_COUNT, $GRID_COL_LOCATION, $GRID_COL_COMPARTMENT, $GRID_COL_COUNT) {
      $currentRow++;
      $newPage = false;
      // Go to next page if reached current page end
      if ($currentRow >= $GRID_ROW_COUNT) {
        $pdf->AddPage();
        $pdf->SetXY($GRID_LEFT, 20);
        $pdf->SetFont('Tahoma', 'B', 10);
        list($fr, $fg, $fb) = sscanf($product['category']['color']['foreground'], "%02x%02x%02x");
        list($br, $bg, $bb) = sscanf($product['category']['color']['background'], "%02x%02x%02x");
        $pdf->SetTextColor($fr, $fg, $fb);
        $pdf->SetFillColor($br, $bg, $bb);
        $catWidth = $pdf->GetStringWidth(utf8_decode($product['category']['name'])) + 2;
        $pdf->Cell($catWidth, 6, utf8_decode($product['category']['name']), 0, 0, "L", true);
        $pdf->SetTextColor(0);
        $pdf->SetFillColor(255);
        $pdf->Cell(170 - $catWidth, 6, utf8_decode($product['name']));
        $pdf->SetFont('Tahoma', 'B', 10);
        $pdf->SetXY($GRID_LEFT, 28);
        $pdf->Cell($GRID_COL_LOCATION, 5, utf8_decode('Emplacement'), 0, 0, "L");
        $pdf->Cell($GRID_COL_COMPARTMENT, 5, utf8_decode('Compartiment'), 0, 0, "L");
        $pdf->Cell($GRID_COL_COUNT, 5, utf8_decode('Quantité'), 0, 0, "L");
        $pdf->SetFont('Tahoma', '', 10);
        $currentRow = 0;
        $newPage = true;
      }
      $pdf->SetXY($GRID_LEFT, $GRID_TOP + $currentRow * $GRID_ROW_HEIGHT);
      return $newPage;
    };

    // Iterate over locations
    foreach ($locations as $location) {

      // Iterate over compartments of given location
      $locationFirst = true;
      foreach (array_keys($location['compartments']) as $compartment) {

        // Start row, if new page then force headers
        if ($nextRow()) {
          $locationFirst = true;
        }

        // Print location if required
        if ($locationFirst) {
          self::printStickers($pdf, $location['location']['stickers']);
          $pdf->Line($GRID_LEFT, $pdf->GetY() - 1, 210 - $GRID_LEFT, $pdf->GetY() - 1);
        }
        $pdf->SetX($GRID_LEFT + $GRID_COL_LOCATION);

        // Print compartment
        $pdf->Cell($GRID_COL_COMPARTMENT, 5, utf8_decode($compartment), 0, 0, "L");

        // Print count
        $pdf->Cell($GRID_COL_COUNT, 5, utf8_decode($location['compartments'][$compartment]), 0, 0, "R");

        $locationFirst = false;
      }
    }

    return $pdf;
  }

  /**
   * Print pipes report
   * @return FPDF PDF file to print
   */
  public static function printPipes()
  {

    // Format of output :
    //
    // SIS Régional Basse-Allaine - Rapport d'inspection des tuyaux
    //
    // Tableau détaillé :
    //
    //                 Roulé séparément                       Dévidoirs
    //                 40(20)  55(20)  75(20)  75(40)   40(100)  55(80)  75(60)
    // Boncourt        160     40      20      240      180      560     1000
    // ...
    // Total           400     600     900     300      500      800     1600
    //
    // Récapitulatif :
    //
    //                 40      55      75
    // Boncourt        840     1200    1500
    // ...
    // Total           1600    3000    5000
    //
    // Rapport établi le 25.02.2017

    // Get locations for which to retrieve pipes infos
    $locations = LocationModel::getLocationsForPipesReport();

    // Determine location IDs
    $locationsIds = Arrays::implode(
      array_values(
        Arrays::flatten(
          Arrays::each(
            $locations,
            function ($location) {
              return Arrays::merge(
                array($location['id']),
                $location['children']
              );
            }
          )
        )
      ),
      ', '
    );

    // Retrieve pipe infos for all locations
    $query = <<<EOF
      SELECT
        COUNT(I.id) AS count,
        I.location_id AS location,
        P.length AS length,
        P.separate AS separate,
        D.diameter AS diameter
      FROM item I
      INNER JOIN product_pipe P ON I.product_id = P.id
      INNER JOIN pipediameter D ON P.diameter_id = D.id
      WHERE I.deleted IS NULL
        AND I.location_id IN ($locationsIds)
      GROUP BY I.location_id, P.length, P.separate, D.diameter
EOF;
    $rows = self::db()->select($query);

    // Consolidate infos
    $details = [
      'total' => []
    ];
    $summary = [
      'total' => []
    ];
    foreach ($rows as $row) {
      // Find parent location corresponding to the row
      $parent = Arrays::find(
        $locations,
        function ($location) use ($row) {
          return (
            $location['id'] === $row->location
            ||
            Arrays::contains($location['children'], $row->location)
          );
        }
      );
      if (!$parent) {
        throw new InternalException(
          InternalException::BAD_IMPLEMENTATION,
          "Unexpected output from database."
        );
      }

      // Build keys
      $keyLocation = 'l' . $parent['id'];
      $keyDetailsType = 't' . $row->separate;
      $keyDetailsCol = 'd' . $row->diameter . 'l' . $row->length;
      $keySummary = 'd' . $row->diameter;

      // If details sub-array does not exist, create
      if (!Arrays::has($details, $keyLocation)) {
        $details[$keyLocation] = [];
      }
      if (!Arrays::has($details[$keyLocation], $keyDetailsType)) {
        $details[$keyLocation][$keyDetailsType] = [];
      }
      if (!Arrays::has($details[$keyLocation][$keyDetailsType], $keyDetailsCol)) {
        $details[$keyLocation][$keyDetailsType][$keyDetailsCol] = 0;
      }
      if (!Arrays::has($details['total'], $keyDetailsType)) {
        $details['total'][$keyDetailsType] = [];
      }
      if (!Arrays::has($details['total'][$keyDetailsType], $keyDetailsCol)) {
        $details['total'][$keyDetailsType][$keyDetailsCol] = 0;
      }

      // Add to details
      $details[$keyLocation][$keyDetailsType][$keyDetailsCol] += ($row->count * $row->length);
      $details['total'][$keyDetailsType][$keyDetailsCol] += ($row->count * $row->length);

      // If summary sub-array does not exist, create
      if (!Arrays::has($summary, $keyLocation)) {
        $summary[$keyLocation] = [];
      }
      if (!Arrays::has($summary[$keyLocation], $keySummary)) {
        $summary[$keyLocation][$keySummary] = 0;
      }
      if (!Arrays::has($summary['total'], $keySummary)) {
        $summary['total'][$keySummary] = 0;
      }

      // Add to summary
      $summary[$keyLocation][$keySummary] += ($row->count * $row->length);
      $summary['total'][$keySummary] += ($row->count * $row->length);
    }

    // Create PDF
    $pdf = self::createPdf();

    // Print title
    $pdf->SetFont('Tahoma', 'B', 14);
    $pdf->SetXY(20, 12);
    $pdf->Cell(170, 10, utf8_decode("Rapport d'inspection des tuyaux"), 0, 0, "C");

    // Print subtitle
    $pdf->SetFont('Tahoma', 'I', 12);
    $pdf->SetXY(20, 22);
    $pdf->Cell(170, 8, utf8_decode("SIS Régional de la Basse-Allaine"), 0, 0, "C");

    // Print title frame
    $pdf->Rect(20, 12, 170, 18);

    // Determine column size
    $colsForLocation = 3; // Number of columns used for location name
    $colMaxSize = 25; // Maximum size of a single column
    $colsCount = $colsForLocation + Arrays::sum(
      Arrays::each(
        $details['total'],
        function ($cols) {
          return Arrays::size($cols);
        }
      )
    );
    $colSize = 170.0 / $colsCount;
    if ($colSize > $colMaxSize) {
      $colSize = $colMaxSize;
    }

    // Print details
    $pdf->SetFont('Tahoma', 'B', 12);
    $pdf->SetXY(20, 50);
    $pdf->Cell(170, 10, utf8_decode("Rapport détaillé"), 0, 0, "L");
    $pdf->SetFont('Tahoma', '', 10);
    $pdf->SetXY(20, 60);
    $pdf->Cell(3 * $colSize - 5, 6, utf8_decode("Type"), 0, 0, "R");
    $pdf->SetXY(20, 66);
    $pdf->Cell(3 * $colSize - 5, 6, utf8_decode("Diamètre (mm)"), 0, 0, "R");
    $pdf->SetXY(20, 72);
    $pdf->Cell(3 * $colSize - 5, 6, utf8_decode("Longueur (m)"), 0, 0, "R");
    $pdf->SetXY(20, 78);
    $locationIds = Arrays::each(
      Arrays::filter(
        array_keys($details),
        function ($key) {
          return $key !== 'total';
        }
      ),
      function ($key) use (&$pdf, &$locations, &$colSize, &$first) {
        list($idx) = sscanf($key, "l%u");
        $location = Arrays::find(
          $locations,
          function ($location) use (&$idx) {
            return $location['id'] === $idx;
          }
        );
        if (!$location) {
          throw new InternalException(
            InternalException::BAD_IMPLEMENTATION,
            "Unexpected behaviour, location is not defined."
          );
        }
        $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
        $pdf->Cell(3 * $colSize, 6, utf8_decode($location['name']), 0, 2, "L");
        return $idx;
      }
    );
    $pdf->SetFillColor(220);
    $pdf->Rect(20 + 3 * $colSize, 78, 170 - 3 * $colSize, 6 * Arrays::size($details), 'F');
    $pdf->SetFillColor(255);
    $pdf->SetFont('Tahoma', 'B', 10);
    $pdf->Line(20, $pdf->GetY(), 190, $pdf->GetY());
    $pdf->Cell(3 * $colSize, 6, utf8_decode("Total"), 0, 2, "L");
    $pdf->SetFont('Tahoma', '', 10);
    $typeX = 20 + 3 * $colSize;
    foreach ($details['total'] as $type => $cols) {
      // Print structure
      $pdf->Rect($typeX, 60, $colSize * Arrays::size($cols), 18 + Arrays::size($details) * 6);

      // Print type
      $pdf->SetXY($typeX, 60);
      $pdf->Cell($colSize * Arrays::size($cols), 6, utf8_decode($type === 't0' ? 'Dévidoir' : 'Roulé séparémment'), 0, 0, "C");

      // Print columns
      $first = true;
      $colX = $typeX;
      foreach ($cols as $key => $val) {
        // Separate diameter and length
        list($diameter, $length) = sscanf($key, 'd%ul%u');

        // Print header
        $pdf->SetFont('Tahoma', '', 10);
        $pdf->SetXY($colX, 66);
        $pdf->Cell($colSize, 6, $diameter, 0, 0, "C");
        $pdf->SetXY($colX, 72);
        $pdf->Cell($colSize, 6, $length, 0, 0, "C");

        // Print rows
        $pdf->SetXY($colX, 78);
        foreach ($locationIds as $locationId) {
          $content = (
            Arrays::has($details, 'l' . $locationId)
            &&
            Arrays::has($details['l' . $locationId], $type)
            &&
            Arrays::has($details['l' . $locationId][$type], $key)
          )
            ? $details['l' . $locationId][$type][$key]
            : '-';
          $pdf->Cell($colSize, 6, $content, 0, 2, "C");
        }

        // Print total
        $pdf->SetXY($colX, 78 + (Arrays::size($details) - 1) * 6);
        $pdf->SetFont('Tahoma', 'B', 10);
        $pdf->Cell($colSize, 6, $val, 0, 0, "C");
        $pdf->SetFont('Tahoma', '', 10);

        // Print line (separator of columns)
        if (!$first) {
          $pdf->Line($colX, 66, $colX, 78 + (Arrays::size($details)) * 6);
        }

        // Determine X for next column
        $colX += $colSize;
        $first = false;
      }

      // Determine X for type big-column
      $typeX += $colSize * Arrays::size($cols);
    }

    // Print summary
    $baseY = $pdf->GetY() + 25;
    $pdf->SetXY(20, $baseY);
    $pdf->SetFont('Tahoma', 'B', 12);
    $pdf->Cell(170, 10, utf8_decode("Résumé par diamètre"), 0, 0, "L");
    $pdf->SetFont('Tahoma', '', 10);
    $baseY += 10;
    $pdf->SetXY(20, $baseY);
    $pdf->Cell(3 * $colSize - 5, 6, utf8_decode("Diamètre (mm)"), 0, 0, "R");
    $pdf->SetXY(20, $baseY + 6);
    foreach ($locationIds as $locationId) {
      $location = Arrays::find(
        $locations,
        function ($location) use ($locationId) {
          return $location['id'] === $locationId;
        }
      );
      if (!$location) {
        throw new InternalException(
          InternalException::BAD_IMPLEMENTATION,
          "Unexpected behaviour, location is not defined."
        );
      }
      $pdf->Line(20, $pdf->GetY(), 20 + (3 + Arrays::size($summary['total'])) * $colSize, $pdf->GetY());
      $pdf->Cell(3 * $colSize, 6, utf8_decode($location['name']), 0, 2, "L");
    }
    $pdf->SetFillColor(220);
    $pdf->Rect(20 + 3 * $colSize, $baseY + 6, Arrays::size($summary['total']) * $colSize, 6 * Arrays::size($summary), 'F');
    $pdf->SetFillColor(255);
    $pdf->SetFont('Tahoma', 'B', 10);
    $pdf->Line(20, $pdf->GetY(), 20 + (3 + Arrays::size($summary['total'])) * $colSize, $pdf->GetY());
    $pdf->Cell(3 * $colSize, 6, utf8_decode("Total"), 0, 2, "L");
    $pdf->SetFont('Tahoma', '', 10);
    $diameterX = 20 + 3 * $colSize;
    foreach ($summary['total'] as $diameter => $total) {
      // Extract diameter
      list($diameter) = sscanf($diameter, "d%u");

      // Print structure
      $pdf->Rect($diameterX, $baseY, $colSize, 6 + Arrays::size($summary) * 6);

      // Print header
      $pdf->SetFont('Tahoma', '', 10);
      $pdf->SetXY($diameterX, $baseY);
      $pdf->Cell($colSize, 6, $diameter, 0, 0, "C");

      // Print rows
      $pdf->SetXY($diameterX, $baseY + 6);
      foreach ($locationIds as $locationId) {
        $content = (
          Arrays::has($summary, 'l' . $locationId)
          &&
          Arrays::has($summary['l' . $locationId], 'd' . $diameter)
        )
          ? $summary['l' . $locationId]['d' . $diameter]
          : '-';
        $pdf->Cell($colSize, 6, $content, 0, 2, "C");
      }

      // Print total
      $pdf->SetXY($diameterX, $baseY + Arrays::size($summary) * 6);
      $pdf->SetFont('Tahoma', 'B', 10);
      $pdf->Cell($colSize, 6, $total, 0, 0, "C");
      $pdf->SetFont('Tahoma', '', 10);

      // Determine X for next diameter
      $diameterX += $colSize;
    }

    // Print footer
    $baseY = $pdf->GetY() + 25;
    $text = "Toutes les cellules grises indiquent des longueurs de tuyaux cumulées, en mètres.";
    $pdf->SetXY(20, $baseY);
    $pdf->SetFillColor(220);
    $pdf->Cell($pdf->GetStringWidth($text), 6, utf8_decode($text), 0, 0, "C", true);
    $pdf->SetFillColor(255);
    $baseY += 20;
    $pdf->SetXY(20, $baseY);
    $date = date("d.m.Y");
    $pdf->Cell(170, 6, utf8_decode("Etabli à Boncourt, le $date"), 0, 0, "L");

    return $pdf;
  }

  /**
   * Get basic PDF file
   * @return FPDF PDF file ready to be filled
   */
  private static function createPdf($autoFirstPage = true, $isLandscape = false)
  {
    $pdf = new FPDF($isLandscape ? 'L' : 'P');
    $pdf->setMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(false);
    $pdf->AddFont('Tahoma', '', 'Tahoma.php');
    $pdf->AddFont('Tahoma', 'B', 'TahomaB.php');
    $pdf->AddFont('Tahoma', 'I', 'TahomaI.php');
    $pdf->AddFont('Tahoma', 'BI', 'TahomaBI.php');
    if ($autoFirstPage) {
      $pdf->AddPage();
    }
    return $pdf;
  }

  private static function printStickers(&$pdf, &$stickers, $height = 5)
  {
    foreach ($stickers as $sticker) {
      list($fr, $fg, $fb) = sscanf($sticker['color']['foreground'], "%02x%02x%02x");
      list($br, $bg, $bb) = sscanf($sticker['color']['background'], "%02x%02x%02x");
      $pdf->SetTextColor($fr, $fg, $fb);
      $pdf->SetFillColor($br, $bg, $bb);
      $pdf->Cell(
        $pdf->GetStringWidth(utf8_decode($sticker['name'])) + 2,
        $height,
        utf8_decode($sticker['name']),
        1,  // with border
        0,
        "C",
        true // with background color
      );
      $pdf->SetX($pdf->GetX() + 1);
    }
    $pdf->SetTextColor(0);
    $pdf->SetFillColor(255);
  }

  /**
   * Print one control group in existing PDF
   * @param object $pdf PDF file to print
   * @param integer $groupId ID of the control group to print
   */
  private static function printControlGroupContent(&$pdf, $groupId)
  {

    // Grid constants
    $GRID_TOP = 12; // number of pixels from top of page before first row of grid
    $GRID_ROW_HEIGHT = 4; // number of pixels defining height of a single grid row
    $GRID_ROW_COUNT = 46; // number of grid rows per page
    $GRID_FONT_SIZE = 9;

    // Grid helpers
    $setGridRow = function ($row) use (&$pdf, $GRID_TOP, $GRID_ROW_HEIGHT) {
      $pdf->SetXY(12, $GRID_TOP + $row * $GRID_ROW_HEIGHT);
    };
    $printCheckBox = function () use (&$pdf, $GRID_ROW_HEIGHT) {
      $pdf->SetLineWidth(0.4);
      $pdf->Rect(285 - $GRID_ROW_HEIGHT, $pdf->GetY() + 0.5, $GRID_ROW_HEIGHT - 1, $GRID_ROW_HEIGHT - 1);
      $pdf->SetLineWidth(0.2);
    };

    // Gather data to print
    $groupData = ControlGroupModel::getControlGroup($groupId);
    $tasksData = ControlTaskModel::listControlTasks($groupData['control']['id']);
    $locationsData = ControlProductModel::listControlLines($groupId);

    // Create first page
    $pdf->AddPage();

    // Print header with basic infos about control group
    $pdf->SetFont('Tahoma', '', 11);
    $pdf->SetXY(12, 12);
    $pdf->Cell(30, 6, utf8_decode("Contrôle"), 0, 0, "L");
    $pdf->Cell(140, 6, utf8_decode($groupData['control']['name']), 0, 0, "L");
    $pdf->SetXY(12, 18);
    $pdf->Cell(30, 6, utf8_decode("Groupe"), 0, 0, "L");
    $pdf->Cell(140, 6, utf8_decode($groupData['name']), 0, 0, "L");
    $pdf->SetXY(12, 24);
    $pdf->Cell(30, 6, utf8_decode("Emplacements"), 0, 0, "L");
    $pdf->SetXY(43, 24.5);
    foreach ($groupData['locations'] as $location) {
      self::printStickers($pdf, $location['stickers']);
      $pdf->SetX($pdf->GetX() + 1);
    }
    $pdf->SetXY(12, 30);
    $pdf->Cell(30, 6, utf8_decode("Responsable"), 0, 0, "L");
    $pdf->Cell(140, 6, utf8_decode($groupData['manager']['name']), 0, 0, "L");
    $pdf->SetXY(12, 36);
    $pdf->Cell(30, 6, utf8_decode("Aides"), 0, 0, "L");
    $pdf->Cell(140, 6, utf8_decode(Arrays::implode(Arrays::each($groupData['helpers'], function ($helper) {
      return $helper['name'];
    }), ', ')), 0, 0, "L");
    $pdf->SetXY(182, 12);
    $pdf->Cell(103, 6, utf8_decode("Imprimé le " . date('d.m.Y')), 0, 0, "L");
    $pdf->SetXY(182, 24);
    $pdf->Cell(103, 6, utf8_decode("Effectué le ___________________"), 0, 0, "L");
    $pdf->SetXY(182, 36);
    $pdf->Cell(103, 6, utf8_decode("Signature   ___________________"), 0, 0, "L");

    // Grid trackers
    $currentRow = 8; // First row after header
    $nextRow = function () use (&$pdf, &$currentRow, $GRID_ROW_COUNT, $setGridRow) {
      $newPage = false;
      $currentRow++;
      if ($currentRow >= $GRID_ROW_COUNT) {
        $currentRow = 0;
        $newPage = true;
        $pdf->AddPage();
      }
      $setGridRow($currentRow);
      return $newPage;
    };

    // Constants for tasks grid (columns)
    $TASK_NAME = 12;
    $TASK_NAME_WIDTH = 200;
    $TASK_CONTACT = $TASK_NAME + $TASK_NAME_WIDTH;
    $TASK_CONTACT_WIDTH = 55;
    $TASK_CHECK = $TASK_CONTACT + $TASK_CONTACT_WIDTH;
    $TASK_CHECK_WIDTH = 18;

    // Helper to print tasks header
    $printTasksHeader = function ($isNewPage = false) use (&$pdf, $nextRow, $GRID_ROW_HEIGHT, $GRID_FONT_SIZE, $TASK_NAME, $TASK_NAME_WIDTH, $TASK_CONTACT, $TASK_CONTACT_WIDTH, $TASK_CHECK, $TASK_CHECK_WIDTH) {
      if (!$isNewPage) {
        $nextRow();
      }
      $pdf->SetFont('Tahoma', 'B', $GRID_FONT_SIZE);
      $pdf->SetY($pdf->GetY() + 2);
      $pdf->SetX($TASK_NAME);
      $pdf->Cell($TASK_NAME_WIDTH, $GRID_ROW_HEIGHT, utf8_decode("Tâche"), 0, 0, "L");
      $pdf->SetX($TASK_CONTACT);
      $pdf->Cell($TASK_CONTACT_WIDTH, $GRID_ROW_HEIGHT, utf8_decode("Entretien"), 0, 0, "L");
      $pdf->SetX($TASK_CHECK);
      $pdf->Cell($TASK_CHECK_WIDTH, $GRID_ROW_HEIGHT, utf8_decode("En ordre"), 0, 0, "R");
      $pdf->SetXY($TASK_NAME, $pdf->GetY() + 5);
      $pdf->SetLineWidth(0.5);
      $pdf->Line($TASK_NAME, $pdf->GetY(), $TASK_CHECK + $TASK_CHECK_WIDTH, $pdf->GetY());
      $pdf->SetLineWidth(0.2);
      $pdf->SetFont('Tahoma', '', $GRID_FONT_SIZE);
      $nextRow();
    };

    // Start with task header
    $printTasksHeader();

    // Print tasks
    foreach ($tasksData as $task) {
      while ($nextRow()) {
        $printTasksHeader(true);
      }
      $pdf->SetX($TASK_NAME);
      $pdf->Cell($TASK_NAME_WIDTH, $GRID_ROW_HEIGHT, utf8_decode($task['description']), 0, 0, "L");
      $pdf->SetX($TASK_CONTACT);
      $taskContact = $task['contact']['type'] === 'SELF'
        ? "Moi-même"
        : $task['contact']['user']['name'];
      $pdf->Cell($TASK_CONTACT_WIDTH, $GRID_ROW_HEIGHT, utf8_decode($taskContact), 0, 0, "L");
      $printCheckBox();
    }

    // Constants for products grid (columns)
    $CTRL_LOCATION = 12;
    $CTRL_LOCATION_WIDTH = 50;
    $CTRL_QUANTITY = $CTRL_LOCATION + $CTRL_LOCATION_WIDTH;
    $CTRL_QUANTITY_WIDTH = 10;
    $CTRL_PRODUCT = $CTRL_QUANTITY + $CTRL_QUANTITY_WIDTH;
    $CTRL_PRODUCT_WIDTH = 70;
    $CTRL_OPERATION = $CTRL_PRODUCT + $CTRL_PRODUCT_WIDTH;
    $CTRL_OPERATION_WIDTH = 70;
    $CTRL_CONTACT = $CTRL_OPERATION + $CTRL_OPERATION_WIDTH;
    $CTRL_CONTACT_WIDTH = 35;
    $CTRL_UID = $CTRL_CONTACT + $CTRL_CONTACT_WIDTH;
    $CTRL_UID_WIDTH = 20;
    $CTRL_CHECK = $CTRL_UID + $CTRL_UID_WIDTH;
    $CTRL_CHECK_WIDTH = 18;

    // Helper to print products header
    $printControlHeader = function ($isNewPage = false) use (&$pdf, $nextRow, $GRID_ROW_HEIGHT, $GRID_FONT_SIZE, $CTRL_LOCATION, $CTRL_LOCATION_WIDTH, $CTRL_QUANTITY, $CTRL_QUANTITY_WIDTH, $CTRL_PRODUCT, $CTRL_PRODUCT_WIDTH, $CTRL_OPERATION, $CTRL_OPERATION_WIDTH, $CTRL_CONTACT, $CTRL_CONTACT_WIDTH, $CTRL_UID, $CTRL_UID_WIDTH, $CTRL_CHECK, $CTRL_CHECK_WIDTH) {
      if (!$isNewPage) {
        $nextRow();
      }
      $pdf->SetFont('Tahoma', 'B', $GRID_FONT_SIZE);
      $pdf->SetY($pdf->GetY() + 2);
      $pdf->SetX($CTRL_LOCATION);
      $pdf->Cell($CTRL_LOCATION_WIDTH, $GRID_ROW_HEIGHT, utf8_decode("Emplacement"), 0, 0, "L");
      $pdf->SetX($CTRL_QUANTITY);
      $pdf->Cell($CTRL_QUANTITY_WIDTH, $GRID_ROW_HEIGHT, utf8_decode("Qté"), 0, 0, "L");
      $pdf->SetX($CTRL_PRODUCT);
      $pdf->Cell($CTRL_PRODUCT_WIDTH, $GRID_ROW_HEIGHT, utf8_decode("Matériel"), 0, 0, "L");
      $pdf->SetX($CTRL_OPERATION);
      $pdf->Cell($CTRL_OPERATION_WIDTH, $GRID_ROW_HEIGHT, utf8_decode("Opération"), 0, 0, "L");
      $pdf->SetX($CTRL_CONTACT);
      $pdf->Cell($CTRL_CONTACT_WIDTH, $GRID_ROW_HEIGHT, utf8_decode("Entretien"), 0, 0, "L");
      $pdf->SetX($CTRL_UID);
      $pdf->Cell($CTRL_UID_WIDTH, $GRID_ROW_HEIGHT, utf8_decode("N°"), 0, 0, "L");
      $pdf->SetX($CTRL_CHECK);
      $pdf->Cell($CTRL_CHECK_WIDTH, $GRID_ROW_HEIGHT, utf8_decode("En ordre"), 0, 0, "R");
      $pdf->SetXY($CTRL_LOCATION, $pdf->GetY() + 5);
      $pdf->SetLineWidth(0.5);
      $pdf->Line($CTRL_LOCATION, $pdf->GetY(), $CTRL_CHECK + $CTRL_CHECK_WIDTH, $pdf->GetY());
      $pdf->SetLineWidth(0.2);
      $pdf->SetFont('Tahoma', '', $GRID_FONT_SIZE);
      $nextRow();
    };

    // Skip 1 row before switching to regular control lines
    $nextRow();

    // If number of remaining rows is <5, skip to next page already
    // and print header
    if ($currentRow + 5 >= $GRID_ROW_COUNT) {
      while (!$nextRow())
        ;
      $printControlHeader(true);
    } else {
      $printControlHeader();
    }

    // Print rows
    foreach ($locationsData as $locationKey => $location) {

      // At the beginning of each location, print location stickers
      // Don't print location line for first location
      $printLocationLine = $locationKey > 0;
      $printLocationStickers = true;

      // Iterate on all rows of location
      foreach ($location['rows'] as $productKey => $row) {

        // Determine quantity and UIDs of sub-rows
        $quantity = is_array($row['uids'])
          ? Arrays::size($row['uids'])
          : $row['uids'];
        $uids = is_array($row['uids'])
          ? $row['uids']
          : array('-');

        // At the beginning of each product, print product infos
        // Don't print product line if first product of location
        $printProductInfos = true;
        $printProductLine = $productKey > 0;

        // Iterate on all UIDs to print
        foreach ($uids as $uid) {

          // Go to next row, and next page if needed
          while ($nextRow()) {
            $printControlHeader(true);
            $printLocationStickers = true;
            $printProductInfos = true;
            $printLocationLine = false;
            $printProductLine = false;
          }

          // Print location line only if needed
          if ($printLocationLine) {
            $pdf->Line($CTRL_LOCATION, $pdf->GetY(), $CTRL_CHECK + $CTRL_CHECK_WIDTH, $pdf->GetY());
            $printLocationLine = false;
          }

          // Print product line only if needed
          if ($printProductLine) {
            $pdf->Line($CTRL_QUANTITY, $pdf->GetY(), $CTRL_CHECK + $CTRL_CHECK_WIDTH, $pdf->GetY());
            $printProductLine = false;
          }

          // Print location stickers only if needed
          if ($printLocationStickers) {
            $pdf->SetXY($CTRL_LOCATION, $pdf->GetY() + 0.3);
            self::printStickers($pdf, $location['location']['stickers'], $GRID_ROW_HEIGHT - 0.6);
            $pdf->SetXY($CTRL_LOCATION, $pdf->GetY() - 0.3);
            $printLocationStickers = false;
          }

          // Print product infos only if needed
          if ($printProductInfos) {
            $pdf->SetX($CTRL_QUANTITY);
            $pdf->Cell($CTRL_QUANTITY_WIDTH, $GRID_ROW_HEIGHT, utf8_decode($quantity), 0, 0, "L");
            $pdf->SetX($CTRL_PRODUCT);
            $pdf->Cell($CTRL_PRODUCT_WIDTH, $GRID_ROW_HEIGHT, utf8_decode($row['product']['name']), 0, 0, "L");
            $pdf->SetX($CTRL_OPERATION);
            $pdf->Cell($CTRL_OPERATION_WIDTH, $GRID_ROW_HEIGHT, utf8_decode(str_replace("\n", " / ", $row['operation'])), 0, 0, "L");
            $pdf->SetX($CTRL_CONTACT);
            $contactName = $row['contact']['type'] === 'SELF'
              ? "Moi-même"
              : $row['contact']['user']['name'];
            $pdf->Cell($CTRL_CONTACT_WIDTH, $GRID_ROW_HEIGHT, utf8_decode($contactName), 0, 0, "L");
            $printProductInfos = false;
          }

          // Print UID and checkbox
          $pdf->SetX($CTRL_UID);
          $pdf->Cell($CTRL_UID_WIDTH, $GRID_ROW_HEIGHT, utf8_decode($uid), 0, 0, "L");
          $printCheckBox();
        }
      }
    }
  }

}
