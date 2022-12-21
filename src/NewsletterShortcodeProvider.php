<?php

namespace Werkbot\Newsletter;

use SilverStripe\Control\Controller;
use SilverStripe\View\Parsers\ShortcodeParser;

class NewsletterShortcodeProvider extends ShortcodeParser{
  /*
    [newsletter,class="form newsletter-form"]
  */
  public static function handle_shortcode($args, $content, $parser, $shortcode, $extra = array()){
    $PopupContent = '';
    // Add Content before form
    if ($content){
      $PopupContent = $parser->parse($content);
    }
    // If class add wrapper div
    if (isset($arguments['class'])){
      $PopupContent .= '<div class="'.$arguments['class'].'">';
    }
    // Get Form HTML
    $PopupContent .= Controller::curr()->NewsletterForm()
      ->setHTMLID("NewsletterForm".rand(0,100))
      ->setFormAction(Controller::join_links(Controller::curr()->owner->Link(), 'NewsletterForm'))
      ->forTemplate();
    // End wrapper div
    if (isset($arguments['class'])){
      $PopupContent .= '</div>';
    }
    // Return full html
    return $PopupContent;
  }
}
