<?php

#	IMDbphp related classes
require_once 'imdbphp/Psr/Log/LoggerInterface.php';

require_once 'imdbphp/Psr/SimpleCache/CacheInterface.php';

require_once 'imdbphp/Monolog/Processor/ProcessorInterface.php';
require_once 'imdbphp/Monolog/Processor/PsrLogMessageProcessor.php';
require_once 'imdbphp/Monolog/Processor/WebProcessor.php';
require_once 'imdbphp/Monolog/Formatter/FormatterInterface.php';
require_once 'imdbphp/Monolog/Formatter/NormalizerFormatter.php';
require_once 'imdbphp/Monolog/Formatter/WildfireFormatter.php';
require_once 'imdbphp/Monolog/Formatter/LineFormatter.php';
require_once 'imdbphp/Monolog/DateTimeImmutable.php';
require_once 'imdbphp/Monolog/Utils.php';
require_once 'imdbphp/Monolog/Handler/HandlerInterface.php';
require_once 'imdbphp/Monolog/Handler/Handler.php';
require_once 'imdbphp/Monolog/ResettableInterface.php';
require_once 'imdbphp/Monolog/Handler/WebRequestRecognizerTrait.php';
require_once 'imdbphp/Monolog/Handler/FormattableHandlerTrait.php';
require_once 'imdbphp/Monolog/Handler/ProcessableHandlerTrait.php';
require_once 'imdbphp/Monolog/Handler/FormattableHandlerInterface.php';
require_once 'imdbphp/Monolog/Handler/ProcessableHandlerInterface.php';
require_once 'imdbphp/Monolog/Handler/AbstractHandler.php';
require_once 'imdbphp/Monolog/Handler/AbstractProcessingHandler.php';
require_once 'imdbphp/Monolog/Logger.php';
require_once 'imdbphp/Monolog/Handler/ErrorLogHandler.php';
require_once 'imdbphp/Monolog/Handler/FirePHPHandler.php';
require_once 'imdbphp/Monolog/Handler/StreamHandler.php';

#	IMDbphp classes
require_once 'imdbphp/Imdb/Exception.php';
require_once 'imdbphp/Imdb/Exception/Http.php';
require_once 'imdbphp/Imdb/Config.php';
require_once 'imdbphp/Imdb/Logger.php';
require_once 'imdbphp/Imdb/Cache.php';
require_once 'imdbphp/Imdb/Request.php';
require_once 'imdbphp/Imdb/Pages.php';
require_once 'imdbphp/Imdb/MdbBase.php';
require_once 'imdbphp/Imdb/Charts.php';
require_once 'imdbphp/Imdb/Parsing.php';
require_once 'imdbphp/Imdb/Person.php';
require_once 'imdbphp/Imdb/PersonSearch.php';
require_once 'imdbphp/Imdb/Title.php';
require_once 'imdbphp/Imdb/TitleSearch.php';
require_once 'imdbphp/Imdb/TitleSearchAdvanced.php';



