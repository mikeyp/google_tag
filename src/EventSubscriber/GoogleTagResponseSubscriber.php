<?php

/**
 * @file
 * Contains GoogleTagResponseSubscriber.
 */

namespace Drupal\google_tag\EventSubscriber;

use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathMatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class GoogleTagResponseSubscriber
 * @package Drupal\google_tag\EventSubscriber
 */
class GoogleTagResponseSubscriber implements EventSubscriberInterface {

  /**
   * The config object for the google_tag settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * An alias manager to find the alias for the current system path.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The path matcher.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Constructs and event subscriber to log request terminations.
   *
   * @param \Drupal\console_logger\RequestLogger $requestLogger
   *   The request logger service.
   */
  public function __construct(ConfigFactoryInterface $configFactory, AliasManagerInterface $alias_manager, PathMatcherInterface $path_matcher, CurrentPathStack $current_path) {
    $this->config = $configFactory->get('google_tag.settings');
    $this->aliasManager = $alias_manager;
    $this->pathMatcher = $path_matcher;
    $this->currentPath = $current_path;
  }


  /**
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   */
  public function addTag(FilterResponseEvent $event) {
    if (!$event->isMasterRequest()) {
      return;
    }

    $request = $event->getRequest();
    $response = $event->getResponse();

    if (!$this->tagApplies($request, $response)) {
      return;
    }

    $container_id = $this->config->get('container_id');
    $container_id = trim(json_encode($container_id), '"');
    $compact = $this->config->get('compact_tag');

    // Insert snippet after the opening body tag.
    $response_text = preg_replace('@<body[^>]*>@', '$0' . $this->getTag($container_id, $compact), $response->getContent(), 1);
    $response->setContent($response_text);
  }

  /**
   * Return the text for the tag.
   *
   * @param string $container_id
   *   The Google Tag manater container ID.
   * @param bool $compact
   *   Whether or not the tag should be compacted (whitespace removed).
   *
   * @return string
   *   The full text of the Google Tag manager script/embed.
   */
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
   *
   */
  private function tagApplies(Request $request, Response $response) {
    $id = $this->config->get('container_id');

    if (empty($id)) {
      // No container ID.
      return FALSE;
    }


    if (!$this->statusCheck($response) && !$this->pathCheck($request)) {
      // Omit snippet based on the response status and path conditions.
      return FALSE;
    }

    return TRUE;

  }

  /**
   * HTTP status code check. This checks to see if status check is even used
   * before checking the status.
   *
   * @param \Symfony\Component\HttpFoundation\Response $response
   *   The response object.
   *
   * @return bool
   *   True if the check is enabled and the status code matches the list of
   *   enabled statuses.
   */
  private function statusCheck(Response $response) {
    static $satisfied;

    if (!isset($satisfied)) {
      $toggle = $this->config->get('status_toggle');
      $statuses = $this->config->get('status_list');

      if (!$toggle) {
        return FALSE;
      }
      else {
        // Get the HTTP response status.
        $status = $response->getStatusCode();
        $satisfied = strpos($statuses, (string) $status) !== FALSE;
      }
    }
    return $satisfied;
  }

  /**
   *
   */
  private function pathCheck(Request $request) {
    static $satisfied;

    if (!isset($satisfied)) {
      $toggle = $this->config->get('path_toggle');
      $pages = Unicode::strtolower($this->config->get('path_list'));

      if (empty($pages)) {
        return ($toggle == GOOGLE_TAG_DEFAULT_INCLUDE) ? TRUE : FALSE;
      }
      else {
        // Compare the lowercase path alias (if any) and internal path.
        $path = trim($this->currentPath->getPath($request), '/');
        $path_alias = Unicode::strtolower($this->aliasManager->getAliasByPath($path));
        $satisfied = $this->pathMatcher->matchPath($path_alias, $pages) || (($path != $path_alias) && $this->pathMatcher->matchPath($path, $pages));
        $satisfied = ($toggle == GOOGLE_TAG_DEFAULT_INCLUDE) ? !$satisfied : $satisfied;
      }
    }

    return $satisfied;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = array('addTag', -500);
    return $events;
  }
}
