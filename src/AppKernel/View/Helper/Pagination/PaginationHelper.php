<?php

/**
 *            'view_helpers' => [
 *                'invokables' => [
 *                    'Config' => 'AppKernel\View\Helper\Config\ConfigHelper', // OR, You can register that in Module.php
 *                    'Utils' => 'AppKernel\View\Helper\Utils\UtilsHelper', // OR, You can register that in Module.php
 *                    'Image' => 'AppKernel\View\Helper\Media\ImageHelper', // OR, You can register that in Module.php
 *                    'Media' => 'AppKernel\View\Helper\Media\MediaHelper', // OR, You can register that in Module.php
 *                // 'Pagination' => 'AppKernel\View\Helper\PaginationHelper', // OR, You can register that in Module.php
 *                // 'strToLower' => 'View\Helper\StrToLower', // OR, You can register that in Module.php
 *                // 'sth_like_that' => 'View\Helper\PaginationHelper', // OR, You can register that in Module.php
 *                ],
 *            ],
 */

namespace AppKernel\View\Helper\Pagination;

use Zend\View\Helper\AbstractHelper;

class PaginationHelper extends AbstractHelper {

    private $resultsPerPage;
    private $totalResults;
    private $results;
    private $baseUrl;
    private $paging;
    private $page;

    public function __invoke($pagedResults, $page, $baseUrl, $resultsPerPage = 10) {
        $this->resultsPerPage = $resultsPerPage;
        $this->totalResults = $pagedResults->count();
        $this->results = $pagedResults;
        $this->baseUrl = $baseUrl;
        $this->page = $page;
        return $this->generatePaging();
    }

    /**
     * Generate paging html
     */
    private function generatePaging() {
        # Get total page count
        $pages = ceil($this->totalResults / $this->resultsPerPage);
        if ($pages):
            # Don't show pagination if there's only one page
            if ($pages == 1) {
                return;
            }
            # Show back to first page if not first page
            if ($this->page != 1) {
                $this->paging = "<li><a href='" . $this->baseUrl . "page/1'>First</a></li>";
            }

            if ($this->page > 1) {
                $this->paging .= "<li><a href='" . $this->baseUrl . "page/" . ($this->page - 1) . "'>Previous</a></li>";
            }

            # Create a link for each page
            $pageCount = 1;
            while ($pageCount <= $pages) {
                $this->paging .= "<li class='" . ($pageCount == $this->page ? 'active' : '') . "'><a href='" . $this->baseUrl . "page/" . $pageCount . "'>" . $pageCount . "</a></li>";
                $pageCount++;
            }
            if ($this->page < $pages) {
                $this->paging .= "<li><a href='" . $this->baseUrl . "page/" . ($this->page + 1) . "'>Next</a></li>";
            }
            # Show go to last page option if not the last page
            if ($this->page != $pages) {
                $this->paging .= "<li><a href='" . $this->baseUrl . "page/" . $pages . "'>Last</a></li>";
            }
            return $this->paging;
        endif;
        return '';
    }

}
