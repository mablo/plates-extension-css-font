<?php

namespace mablo\Plates\Extension\Css;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class Font implements ExtensionInterface
{
    /**
     * @var string
     */
    private $directory;

    /**
     * Font constructor.
     * @param string $directory
     */
    public function __construct(string $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @param Engine $engine
     */
    public function register(Engine $engine)
    {
        $engine->registerFunction('loadFontFace', [$this, 'loadFontFace']);
    }

    /**
     * @param string $name
     * @param array $fonts
     * @param string $weight
     * @param string $style
     * @return string
     */
    public function loadFontFace(
        string $name,
        array $fonts,
        string $weight = 'normal',
        string $style = 'normal'
    ) : string
    {
        $declaration = <<<DECLARATION
@font-face {
    font-family: '%s';
    %s
    font-weight: %s;
    font-style: %s;
}
DECLARATION;

        return sprintf($declaration, $name, $this->loadFont($fonts), $weight, $style);
    }

    /**
     * @param array $fontPaths
     * @return string
     */
    private function loadFont(array $fontPaths) : string
    {
        return 'src: '. implode(
                ', ',
                array_filter(
                    array_map(
                        function ($fontPath) {
                            $path = $this->directory . '/' . $fontPath;
                            if (file_exists($path)) {
                                $ext = pathinfo($path, PATHINFO_EXTENSION);

                                switch ($ext) {
                                    case 'woff':
                                    case 'woff2':
                                        $contentType = 'application/font-' . $ext;
                                        $format = $ext;
                                        break;
                                    default:
                                        throw new \Exception('Invalid font type');
                                }

                                return sprintf(
                                    'url(data:%s;charset=utf-8;base64,%s) format(\'%s\')',
                                    $contentType,
                                    $this->fontEncode($path),
                                    $format
                                );
                            }

                            return '';
                        },
                        $fontPaths
                    ),
                    function ($item) {
                        return !empty($item);
                    }
                )
            ) . ';';
    }

    /**
     * @param string $fontPath
     * @return string
     */
    private function fontEncode(string $fontPath) : string
    {
        return base64_encode(
            file_get_contents($fontPath)
        );
    }
}