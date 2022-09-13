<?php
/***
 *
 * This file is part of Qc Comments project.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2022 <techno@quebec.ca>
 *
 ***/

namespace Qc\QcComments\View;

use TYPO3\CMS\Extbase\Mvc\View\AbstractView;

class CsvView extends AbstractView
{

    /**
     * @var string
     */
    protected string $filename = 'data.csv';

    /**
     * @var string
     */
    protected string $delimiter = ',';

    /**
     * @var string
     */
    protected string $enclosure = '""';

    /**
     * @var string
     */
    protected string $escapeChar = '\\';

    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * @param array $headers
     * @return CsvView
     */
    public function setHeaders(array $headers): CsvView
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @param string $escapeChar
     * @return CsvView
     */
    public function setEscapeChar(string $escapeChar): CsvView
    {
        $this->escapeChar = $escapeChar;
        return $this;
    }

    /**
     * @param $delimiter
     * @return CsvView
     */
    public function setDelimiter($delimiter): CsvView
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    /**
     * @param $enclosure
     * @return CsvView
     */
    public function setEnclosure($enclosure): CsvView
    {
        $this->enclosure = $enclosure;
        return $this;
    }

    /**
     * @param $filename
     * @return CsvView
     */
    public function setFilename($filename): CsvView
    {
        $this->filename = $filename;
        return $this;
    }
    /**
     * Renders the view
     *
     * @return string The rendered view
     * @api
     */
    public function render()
    {
        $response = $this->controllerContext->getResponse();
        $response->setHeader('Content-Type', 'text/csv; charset=utf-8');
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $this->filename);
        $rows = $this->variables['rows'];
        $headers = $this->variables['headers'] ?? $this->headers ?? array_keys($rows[0]);

        $fp = fopen('php://temp', 'r+');
        // BOM utf-8 pour excel
        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, $headers, $this->delimiter, $this->enclosure, $this->escapeChar);
        foreach ($rows as $row) {
            array_walk($row, function (&$field) {
                $field = str_replace("\r", ' ', $field);
                $field = str_replace("\n", ' ', $field);
            });
            fputcsv($fp, $row, $this->delimiter, $this->enclosure, $this->escapeChar);
        }
        rewind($fp);
        $str_data = rtrim(stream_get_contents($fp), "\n");
        fclose($fp);
        return $str_data;
    }
}
