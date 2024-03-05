<?php

namespace App\Services;

use Illuminate\Support\Facades\Blade;

class CommonService
{

    /**
     * @param int $length
     * @param bool $strToUpper
     * @param bool $underBar
     * @return string
     */
    public function GenerateString(int $length, bool $strToUpper = true, bool $underBar = true): string
    {
        $characters = "0123456789";
        $characters .= "abcdefghijklmnopqrstuvwxyz";
        if ($strToUpper) {
            $characters .= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        }
        if ($underBar) {
            $characters .= "_";
        }
        $string_generated = "";
        $nmr_loops = $length;
        while ($nmr_loops --) {
            $string_generated .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string_generated;
    }

    /**
     * @param string $fixCode
     * @return string
     */
    public function getRandomCode(string $fixCode = 'user'): string
    {
        $randCode = $this->GenerateString(20);
        return $fixCode . '-' . date("YmdHis") . '-' . $randCode;
    }

    /**
     * @param int $total
     * @param int $page
     * @param int $limit
     * @param int $block
     * @return array
     */
    public function getPagination(int $total, int $page, int $limit, int $block): array
    {
        $totalPage = ceil($total/ $limit);
        $totalBlock = ceil($totalPage / $block);
        $blockGroup = ceil($page / $block);

        $lastNumber = $blockGroup * $block;
        if ($lastNumber > $totalPage) {
            $lastNumber = $totalPage;
        }
        $firstNumber = ($blockGroup -1) * $block +1;
        return [
            'page' => $page,
            'firstNumber' => $firstNumber,
            'lastNumber' => $lastNumber,
            'next' => $lastNumber +1,
            'prev' => $firstNumber -1,
            'totalPage' => $totalPage,
        ];
    }

    /**
     * @param int $total
     * @param int $page
     * @param int $limit
     * @param int $block
     * @return string
     */
    public function getPaginationHtml(int $total, int $page, int $limit, int $block): string
    {
        $pagination = $this->getPagination($total, $page, $limit, $block);
        return Blade::render('include.pagination', ['pagination' => $pagination]);
    }
}
