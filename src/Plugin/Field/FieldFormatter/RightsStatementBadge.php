<?php

namespace Drupal\islandora_rights_statements\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Component\Utility\Xss;

/**
 * Plugin implementation of the 'islandora_rights_statements' formatter.
 *
 * @FieldFormatter(
 *   id = "islandora_rights_statements",
 *   label = @Translation("Islandora Rights Statement Badge"),
 *   field_types = {
 *     "string",
 *     "text",
 *     "text_long",
 *     "text_with_summary"
 *   }
 * )
 */
class RightsStatementBadge extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'button_style' => 'buttons',
      'image_height' => '31',
      'image_color' => 'dark',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['button_style'] = [
      '#type' => 'radios',
      '#title' => $this->t('Image style'),
      '#description' => $this->t('The type of image returned as your Rights Statements badge.'),
      '#options' => ['icons' => $this->t('Small icon'), 'buttons' => $this->t('Large button with text')],
      '#default_value' => $this->getSetting('button_style') ?? 'buttons',
    ];

    $form['image_height'] = [
      '#type' => 'number',
      '#title' => $this->t('Image Height'),
      '#description' => $this->t('The height in pixels for the Rightsstatements badge image.'),
      '#default_value' => $this->getSetting('image_height') ?? '31',
    ];

    $form['image_color'] = [
      '#type' => 'select',
      '#title' => $this->t('Image Colour'),
      '#description' => $this->t('The color of the text and image in the Rightsstatements badge.'),
      '#options' => [
        'dark' => $this->t('Black with transparent icon interior'),
        'white' => $this->t('All white'),
        'dark-white-interior' => $this->t('Black with white icon interior'),
        'dark-white-interior-blue-type' => $this->t('Black with blue type (Button style only)'),
      ],
      '#default_value' => $this->getSetting('image_color') ?? 'dark',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Button Style: @field', ['@field' => $this->getSetting('button_style')]);
    $summary[] = $this->t('Image height: @field', ['@field' => $this->getSetting('image_height')]);
    $summary[] = $this->t('Image color: @field', ['@field' => $this->getSetting('image_color')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => $item) {
      $elements[$delta] = $this->getBadge($item->value);
    }
    return $elements;
  }

  /**
   * Take URI, return drupal render element for a rights statement badge.
   */
  public function getBadge($uri) : array {
    $height = $this->getSetting('image_height');
    $color = $this->getSetting('image_color');
    $style = $this->getSetting('button_style');

    $uri = strip_tags($uri);
    $uri = trim($uri);

    // Just print the text if this isn't a rights statement URI.
    if (filter_var($uri, FILTER_VALIDATE_URL) === FALSE || strpos($uri, '/vocab/') === FALSE) {
      return ['#markup' => Xss::filter($uri)];
    }

    // Extract just the statement terms from the URI to use in image URL.
    $terms = substr($uri, strpos($uri, "/vocab/") + 7);
    $terms = substr($terms, 0, strlen($terms) - 5);

    if ($style == 'icons') {
      $style_filename = "Icon-Only.";
      // In case an unavailable color is picked.
      if ($color == 'dark-white-interior-blue-type') {
        $color = 'dark-white-interior';
      }
      // Choose appropriate icon since they are less precise than buttons.
      if ($terms == 'CNE' || $terms == 'UND' || $terms == 'NKC') {
        $terms = 'Other';
      }
      elseif (strpos($terms, 'InC') !== FALSE) {
        $terms = 'InC';
      }
      elseif (strpos($terms, 'NoC') !== FALSE) {
        $terms = 'NoC';
      }
    }
    else {
      $style_filename = "";
    }
    $imagepath = \Drupal::service('extension.list.module')->getPath('islandora_rights_statements');
    $image = '/' . $imagepath . '/images/' . $style . '/' . $terms . '.' . $style_filename . $color . '.png';

    $options = [
      'headers' => [
        'Accept' => 'application/json+ld',
      ],
    ];

    $client = \Drupal::httpClient();
    try {
      $response = $client->request('GET', $uri, $options);
    }
    catch (\Exception $e) {
      return ['#markup' => $uri];
    }
    $result = json_decode($response->getBody()->getContents(), TRUE);

    $preferredLanguage = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    $preferredLanguage = substr(strtok(strip_tags($preferredLanguage), ','), 0, 2);
    $possibleLanguages = ['de', 'et', 'fi', 'fr', 'pl', 'en', 'sv-fi', 'es'];
    $language = in_array($preferredLanguage, $possibleLanguages) ? $preferredLanguage : 'en';

    $titles = $result['prefLabel'];
    $titleValue = '';
    foreach ($titles as $title) {
      if ($title['@language'] == $language) {
        $titleValue = $title['@value'];
        break;
      }
    }

    $altText = $titleValue;
    $badge = [
      '#type' => 'link',
      '#title' => [
        '#type' => 'html_tag',
        '#tag' => 'img',
        '#attributes' => [
          'src' => $image,
          'alt' => $altText,
          'height' => $height,
          'title' => $titleValue,
        ],
      ],
      '#url' => Url::fromUri($uri),
      '#options' => [
        'attributes' => [
          'target' => '_blank',
        ],
        'html' => TRUE,
      ],
    ];

    return $badge;
  }

}
