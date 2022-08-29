<?php declare(strict_types=1);

$output = new Output();

$opts = getopt("", ['env::', 'shopware:']);

if (!$opts) {
    $output->error('No options set. Option "env" is required.');
    return;
}

switch ($opts['env']) {
    case 'dev':
    case 'develop':
    case 'development':
        $env = 'development';
        $require = 'require-dev';
        break;
    case 'prod':
    case 'production':
        $env = 'production';
        $require = 'require';
        break;
}

if(!isset($require)) {
    $output->error('Env needs to be one of: dev, develop, development, prod, production');
    return;
}

$shopware = '*';

if(isset($opts['shopware'])) {
    $shopware = (string)$opts['shopware'];
}

try {
    $composerContent = file_get_contents(__DIR__ . '/composer.json');
    if(empty($composerContent)) {
        throw new \Exception('Something went wrong reading the composer.json string. Exiting');
    }

    $currentEncoding = mb_detect_encoding($composerContent, ['UTF-8', 'ISO-8859-1'], true);

    switch($currentEncoding) {
        case 'UTF-8': // Already UTF-8, do nothing
            break;
        case 'ISO-8859-1':
            $output->warn('Detected ISO-8859-1 encoding. Attempting to switch to UTF-8');

            $composerContent = mb_convert_encoding($composerContent, 'UTF-8', 'ISO-8859-1');
            break;
        default: // Unknown encoding, warn user they should convert manually.
            throw new \Exception('Unable to detect current composer.json encoding. Please convert to UTF-8 manually.');
    }

    $composerContent = json_decode($composerContent, true);
    if(empty($composerContent)) {
        throw new \Exception('Something went wrong decoding the composer.json string. Exiting');
    }

    unset($composerContent['require']['shopware/core']);
    unset($composerContent['require']['shopware/administration']);
    unset($composerContent['require']['shopware/storefront']);
    unset($composerContent['require-dev']['shopware/core']);
    unset($composerContent['require-dev']['shopware/administration']);
    unset($composerContent['require-dev']['shopware/storefront']);

    if(empty($composerContent['require'])) {
        unset($composerContent['require']);
    }

    if(empty($composerContent['require-dev'])) {
        unset($composerContent['require-dev']);
    }

    $composerContent[$require]['shopware/core'] = $shopware;
    $composerContent[$require]['shopware/administration'] = $shopware;
    $composerContent[$require]['shopware/storefront'] = $shopware;

    file_put_contents(__DIR__ . '/composer.json', json_encode($composerContent, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

    $output->success(sprintf('Switched composer.json to %s requiring Shopware version %s', $env, $shopware));
} catch (\Exception $e) {
    $output->error($e->getMessage());
}

class Output
{
    const COLORS = [
        'black'   => 0,
        'red'     => 1,
        'green'   => 2,
        'yellow'  => 3,
        'blue'    => 4,
        'magenta' => 5,
        'cyan'    => 6,
        'white'   => 7,
        'default' => 9,
    ];

    public function info($text)
    {
        $this->writeLn($this->createBlock([$text]), self::COLORS['black'], self::COLORS['cyan']);
        $this->writeLn();
    }

    public function success($text)
    {
        $this->writeLn($this->createBlock([$text]), self::COLORS['black'], self::COLORS['green']);
        $this->writeLn();
    }

    public function warn($text)
    {
        $this->writeLn($this->createBlock([$text]), self::COLORS['black'], self::COLORS['yellow']);
        $this->writeLn();
    }

    public function error($text)
    {
        $this->writeLn($this->createBlock([$text]), self::COLORS['white'], self::COLORS['red']);
        $this->writeLn();
    }

    public function writeLn($messages = "", $fg = self::COLORS['default'], $bg = self::COLORS['default'])
    {
        if (!is_iterable($messages)) {
            $messages = [$messages];
        }

        $fg = '3' . $fg;
        $bg = '4' . $bg;

        foreach ($messages as $message) {
            echo sprintf("\033[%s;%sm%s\033[0m%s", $fg, $bg, $message, PHP_EOL);
        }
    }

    public function createBlock(iterable $messages, int $indentLength = 2)
    {
        $lines = [];
        $lineLength = 80;

        $lineIndentation = str_repeat(' ', $indentLength);

        foreach ($messages as $message) {
            $messageLineLength = $lineLength - ($indentLength * 2);
            $messageLines = explode(\PHP_EOL, wordwrap($message, $messageLineLength, \PHP_EOL, true));

            foreach ($messageLines as $messageLine) {
                $lines[] = $messageLine;
            }
        }

        array_unshift($lines, '');
        $lines[] = '';

        foreach ($lines as &$line) {
            $line = $lineIndentation . $line;
            $line .= str_repeat(' ', max($lineLength - strlen($line), 0));
        }

        return $lines;
    }
}
