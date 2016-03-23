<?php

/**
 * CacheError class
 * @author liangtaohy@163.com
 * @date 16/3/10 22:35
 */
class CacheError
{
    const E_CACHED_SUCCESS                 = 300;
    const E_CACHED_FAILURE                 = 301;
    const E_CACHED_HOST_LOOKUP_FAILURE     = 302; // getaddrinfo() and getnameinfo() only
    const E_CACHED_CONNECTION_FAILURE      = 303;
    const E_CACHED_CONNECTION_BIND_FAILURE = 304; // DEPRECATED see E_CACHED_HOST_LOOKUP_FAILURE
    const E_CACHED_WRITE_FAILURE           = 305;
    const E_CACHED_READ_FAILURE            = 306;
    const E_CACHED_UNKNOWN_READ_FAILURE    = 307;
    const E_CACHED_PROTOCOL_ERROR          = 308;
    const E_CACHED_CLIENT_ERROR            = 309;
    const E_CACHED_SERVER_ERROR            = 310;// Server returns "SERVER_ERROR"
    const E_CACHED_ERROR                   = 311; // Server returns "ERROR"
    const E_CACHED_DATA_EXISTS             = 312;
    const E_CACHED_DATA_DOES_NOT_EXIST     = 313;
    const E_CACHED_NOTSTORED               = 314;
    const E_CACHED_STORED                  = 315;
    const E_CACHED_NOTFOUND                = 316;
    const E_CACHED_MEMORY_ALLOCATION_FAILURE   = 317;
    const E_CACHED_PARTIAL_READ            = 318;
    const E_CACHED_SOME_ERRORS             = 319;
    const E_CACHED_NO_SERVERS              = 320;
    const E_CACHED_END                     = 321;
    const E_CACHED_DELETED                 = 322;
    const E_CACHED_VALUE                   = 323;
    const E_CACHED_STAT                    = 324;
    const E_CACHED_ITEM                    = 325;
    const E_CACHED_ERRNO                   = 326;
    const E_CACHED_FAIL_UNIX_SOCKET        = 327; // DEPRECATED
    const E_CACHED_NOT_SUPPORTED           = 328;
    const E_CACHED_NO_KEY_PROVIDED         = 329; /* Deprecated. Use E_CACHED_BAD_KEY_PROVIDED! */
    const E_CACHED_FETCH_NOTFINISHED       = 330;
    const E_CACHED_TIMEOUT                 = 331;
    const E_CACHED_BUFFERED                = 332;
    const E_CACHED_BAD_KEY_PROVIDED        = 333;
    const E_CACHED_INVALID_HOST_PROTOCOL   = 334;
    const E_CACHED_SERVER_MARKED_DEAD      = 335;
    const E_CACHED_UNKNOWN_STAT_KEY        = 336;
    const E_CACHED_E2BIG                   = 337;
    const E_CACHED_INVALID_ARGUMENTS       = 338;
    const E_CACHED_KEY_TOO_BIG             = 339;
    const E_CACHED_AUTH_PROBLEM            = 340;
    const E_CACHED_AUTH_FAILURE            = 341;
    const E_CACHED_AUTH_CONTINUE           = 342;
    const E_CACHED_PARSE_ERROR             = 343;
    const E_CACHED_PARSE_USER_ERROR        = 344;
    const E_CACHED_DEPRECATED              = 345;
    const E_CACHED_IN_PROGRESS             = 346;
    const E_CACHED_SERVER_TEMPORARILY_DISABLED = 347;
    const E_CACHED_SERVER_MEMORY_ALLOCATION_FAILURE    = 348;
    const E_CACHED_MAXIMUM_RETURN          = 349; /* Always add new error code before */
}