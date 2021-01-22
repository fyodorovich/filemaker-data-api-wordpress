<?php

namespace FMDataAPI;

class ShortCodeBase {

    /** @var FileMakerDataAPI */
    protected $api;

    /** @var Settings */
    protected $settings;

    public function __construct(FileMakerDataAPI $api, Settings $settings) {
        $this->api = $api;
        $this->settings = $settings;
    }

    protected function outputField(array $record, $field, $type = null, $link = false) {
        if (!array_key_exists($field, $record)) {
            return '';
        }

        switch (substr(strtolower($type), 0, 5)) {
            case 'image':
            case 'thumb':
                $content = $this->sizeImage($type, $record[$field]);
                break;
            case 'curre':
                if (empty($record[$field])) {
                    $content = '';
                    $content = (money_format('%#10n', 0));
                } else {
                    setlocale(LC_ALL, $this->settings->getLocale());
                    $content = (money_format('%#10n', $record[$field]));
                }
                break;
            default:
                $content = nl2br($record[$field]);
        }

        if ($link) {
            return sprintf('<a href="%s">%s</a>', $link, $content);
        }

        return $content;
    }

    protected function sizeImage($type, $path) {
        $params = explode('-', $type);
        $width = 'image' == $params[0] ? (isset($params[1]) ? $params[1] : '') : (isset($params[1]) ? $params[1] : '50');

        return sprintf('<img src="%s" width="%s" />', $path, $width);
    }

    protected function validateAttributesOrExit(array $reqs, array $attr) {
        $err = [];
        foreach ($reqs as $req) {
            if (!array_key_exists($req, $attr)) {
                $err[] = $req;
            }
        }

        if (count($err)) {
            print(sprintf('Error required attribute%s %s missing.', 1 == count($err) ? '' : 's', implode(',', $err)));

            return false;
        }

        return true;
    }

    /**
     * Convert MM/DD/YYYY to DD/MM/YYYY
     * @param type $date
     * @return string
     */
    protected function fmDate2nzDate($date) {
        if (!empty($date)) {
            $us_date = explode("/", $date);
            $nz_date = implode("/", [$us_date[1], $us_date[0], $us_date[2]]);
        } else {
            $nz_date = '';
        }
        return $nz_date;
    }

    /**
     * Ensure that dollar amounts are two decimal places with $ prefix
     * @param type $field
     * @return string
     */
    protected function formatCurrency($field, $returnZero = false) {

        if (empty($field) || 0 == $field) {
            $content = $returnZero ? '0.00' : '';
        } else {
            if (class_exists('NumberFormatter')) {
                $fmt = new \NumberFormatter($this->settings->getLocale(), NumberFormatter::CURRENCY);
                $content = $fmt->formatCurrency($field, "NZ");
            } else {
               //  $content =  number_format($field, 2);
                setlocale(LC_MONETARY, 'en_NZ.UTF-8');
                $content = money_format('%.2n', $field);
            }
        }
        return $content;
    }

    protected function connectionError($reload = false) {
        if ($reload) {
            return 'Unable to load records. Please reload the page.';
        } else {
            return '<div class="error-alert">That\'s embarrassing!<br>We cannot locate any contract information for your account. Please contact Adelphi finance<br>Phone: <a href="tel:043852502">04 385 2502</a> or email <a href="mailto:info@adelphi.co.nz">info@adelphi.co.nz</a></div>';
        }
    }

}
