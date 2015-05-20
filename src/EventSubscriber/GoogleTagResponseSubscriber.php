<?php

/**
 * @file
 * Contains GoogleTagResponseSubscriber.
 */

namespace Drupal\google_tag\EventSubscriber;

use Masterminds\HTML5;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class GoogleTagResponseSubscriber
 * @package Drupal\google_tag\EventSubscriber
 */
class GoogleTagResponseSubscriber implements EventSubscriberInterface {

  /**
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   */
  public function addTag(FilterResponseEvent $event) {
    if (!$event->isMasterRequest()) {
      return;
    }

//    $container_id = variable_get('google_tag_container_id', '');
//    $container_id = trim(drupal_json_encode($container_id), '"');
//    $compact = variable_get('google_tag_compact_tag', 1);

    $response = $event->getResponse();

    // Insert snippet after the opening body tag.
    $response_text = preg_replace('@<body[^>]*>@', '$0' . $this->getTag($container_id, $compact), $response->getContent(), 1);
    $response->setContent($response_text);
  }

  public function getTag($container_id, $compact = FALSE) {
    // Build script tags.
    $noscript = <<<EOS
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=$container_id"
 height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
EOS;
    $script = <<<EOS
<script type="text/javascript">
(function(w,d,s,l,i){

  w[l]=w[l]||[];
  w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
  var f=d.getElementsByTagName(s)[0];
  var j=d.createElement(s);
  var dl=l!='dataLayer'?'&l='+l:'';
  j.src='//www.googletagmanager.com/gtm.js?id='+i+dl;
  j.type='text/javascript';
  j.async=true;
  f.parentNode.insertBefore(j,f);

})(window,document,'script','dataLayer','$container_id');
</script>
EOS;


    if ($compact) {
      $noscript = str_replace("\n", '', $noscript);
      $script = str_replace(array("\n", '  '), '', $script);
    }
    $script = <<<EOS

<!-- Google Tag Manager -->
$noscript
$script
<!-- End Google Tag Manager -->
EOS;

    return $script;

  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = array('addTag', -500);
    return $events;
  }
}
