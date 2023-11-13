<?php


namespace rokpeterlin\phpmvc;


class Session
{

  protected const FLASH_KEY = 'flash_messages';

  public function __construct()
  {
    session_start();
    $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];

    // &$var allows us to modify variable in-place.
    foreach ($flashMessages as $key => &$flashMessage) {
      // Flash messages only persist for one request. As soon as we make another request current flash massages need to be removed. So we mark the flash messages here.
      $flashMessage['remove'] = true;
    }

    $_SESSION[self::FLASH_KEY] = $flashMessages;
  }

  public function __destruct()
  {
    // Iterate over marked to-be-removed flash messages and remove them.

    $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];
    foreach ($flashMessages as $key => &$flashMessage) {
      if ($flashMessage['remove']) {
        unset($flashMessages[$key]);
      }
    }
    $_SESSION[self::FLASH_KEY] = $flashMessages;
  }

  public function setFlash($key, $message)
  {
    $_SESSION[self::FLASH_KEY][$key] = [
      'remove' => false,
      'value' => $message,
    ];
  }

  public function getFlash($key)
  {
    return $_SESSION[self::FLASH_KEY][$key]['value'] ?? false;
  }

  public function set($key, $value)
  {
    $_SESSION[$key] = $value;
  }

  public function get($key)
  {
    return $_SESSION[$key] ?? false;
  }

  public function remove($key)
  {
    unset($_SESSION[$key]);
  }
}
