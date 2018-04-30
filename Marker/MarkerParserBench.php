<?php
/**
 * Created by PhpStorm.
 * User: otterdijk
 * Date: 4/29/18
 * Time: 10:21 PM
 */

namespace PhpDocumentor\Marker;

/**
 * @BeforeMethods({"init"})
 * @Revs({1, 8, 64, 4096})
 */
final class MarkerParserBench
{
    /** @var string source of file to process */
    private $source;

    private $markerTerms = ['@TODO', 'FIXME'];

    public function init()
    {
        $this->source = file_get_contents(__DIR__ . '/../assets/PhpMailer.php');
    }

    public function benchPregMatchOffset()
    {
        $marker_data = array();
        preg_match_all(
            '~//[\s]*(' . implode('|', $this->markerTerms) . ')\:?[\s]*(.*)~',
            $this->source,
            $matches,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE
        );

        foreach ($matches as $match) {
            list($before) = str_split($this->source, $match[1][1]); // fetches all the text before the match

            $line_number = strlen($before) - strlen(str_replace("\n", "", $before)) + 1;

            $marker_data[] = ['type' => trim($match[1][0], '@'), 'line' => $line_number, $match[2][0]];
        }
    }

    public function benchExplodeLines()
    {
        $marker_data = [];
        foreach (explode("\n", $this->source) as $line_number => $line) {
            preg_match_all(
                '~//[\s]*(' . implode('|', $this->markerTerms) . ')\:?[\s]*(.*)~',
                $line,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as &$match) {
                $match[3] = $line_number + 1;
            }
            $marker_data = array_merge($marker_data, $matches);
        }

        // store marker results and remove first entry (entire match),
        // this results in an array with 2 entries:
        // marker name and content
        foreach ($marker_data as &$marker) {
            array_shift($marker);
        }
    }
}
