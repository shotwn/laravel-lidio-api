<?php

namespace Shotwn\LidioAPI\Exceptions;

/**
 * Invalid OrderId parameter value. This result code will be returned if these conditions are not met;
 * • The “OrderId” parameter can not be null or empty.
 * • If the merchant uses Yapikredi bank VPOS account maximum 20 character is allowed.
 * • If the merchant uses Garanti bank VPOS account maximum 36 character is allowed.And “-” character is not allowed in OrderId
 * • In general maximum 36 character is allowed.
 */
class LidioInvalidOrderIdException extends LidioAPIException
{
}
