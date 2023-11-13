<?php


namespace rokpeterlin\phpmvc\exception;


// extends core PHP Exception class
class ForbiddenException extends \Exception
{
  protected $message = "You don't have permission to access this page";
  protected $code = 403;
}
