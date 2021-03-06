<?php
class Comments_Paginator
{
    const NUM_PLACEHOLDER = '(:num)';

    protected $totalItems;
    protected $numPages;
    protected $itemsPerPage;
    protected $currentPage;
    protected $urlPattern;
    protected $maxPagesToShow = 10;
    protected $previousText = 'Previous';
    protected $nextText = 'Next';
    protected $table = 'comments';
    protected $post_id_col = 'post_id';
    protected $parent_id_col = 'parent_id';
    protected $conn;
    protected $post_id;

    /**
    * Construct
     */
    public function __construct($conn, $post_id, $param = [])
    {
        $this->conn = $conn;
        $this->post_id = $post_id;

        $this->totalItems = $this->getTotalComments($this->post_id);
        $this->itemsPerPage = $param['itemsPerPage'] ?? 25;
        $this->currentPage = $param['currentPage'] ?? 1;
        $this->urlPattern = $param['urlPattern'] ?? "?page=(:num)";

        $this->updateNumPages();
    }

    /**
    * Construct
     */
    public function getComments()
    {
    	$mysqli = $this->conn;

		$childComments = $this->_getComments(
			$this->post_id, 
			"AND ({$this->parent_id_col} != 0 AND {$this->parent_id_col} IS NOT NULL)"
		);

		$currentPage = $this->currentPage ?? 1;
		$itemsPerPage = $this->itemsPerPage ?? 25;
		$offset = ($currentPage - 1) * $itemsPerPage;

		$parentComments = $this->_getComments(
			$this->post_id, 
			"AND ({$this->parent_id_col} = 0 OR {$this->parent_id_col} IS NULL)",
			" LIMIT {$offset}, {$itemsPerPage}"
		);

		foreach ($parentComments as $comment) {
			$comment['childs'] = $this->buildTree($childComments, $comment['id']);
			$comments[] = $comment;
		}	

		return $comments;
    }

    /**
    * Construct
     */
    public function getTotalComments($post_id, $where = "")
    {
    	$mysqli = $this->conn;

		$result = $mysqli->query(
			"SELECT COUNT(*) AS count FROM {$this->table} WHERE 1=1 AND {$this->post_id_col} = {$post_id} AND ({$this->parent_id_col} = 0 OR {$this->parent_id_col} IS NULL) {$where}"
		);

		$total_comments = $result->fetch_object()->count;
		return $total_comments;
    }

    /**
    * Construct
     */
    public function _getComments($post_id, $where = "", $limit = "")
    {
    	$mysqli = $this->conn;

		$where = " WHERE 1=1 AND {$this->post_id_col} = {$post_id} {$where}";
		$sql = "SELECT * FROM {$this->table} {$where} ORDER BY id DESC";
		$sql .= " {$limit}";

		$result = $mysqli->query($sql);

		$comments = [];
		while ($row = $result->fetch_assoc()) {
			$comments[] = $row;
		}	

		return $comments;
    }

    public function buildTree(array $rows, $parent_id = 0) {
	    $tree = array();

	    foreach ($rows as $row) {
	        if ( $row[ $this->parent_id_col ] == $parent_id ) {
	            $childs = $this->buildTree($rows, $row['id']);
	            
	            if (!empty($childs)) {
	                $row['childs'] = $childs;
	            }

	            $tree[] = $row;
	        }
	    }

	    return $tree;
	}

	/**
    * Construct
     */
    public function getCommentsHtml()
    {
    	$comments = $this->getComments();

    	$html = "<h3>Total Comments {$this->totalItems}</h3>";
    	$html .= "<div class=\"comments\">";
    	foreach ($comments as $key => $comment) {
    		$html .= $this->getCommentHtml($comment, 1);
    	}
    	$html .= "</div>";

		return $html;
    }

    /**
    * Construct
     */
    public function getCommentHtml($comment, $level = 1)
    {   
        // Dummy Values
        $comment['class'] = 1;
        $comment['cur_class'] = 1;
        $comment['username'] = 'Shahid';
        $comment['cur_id'] = 1;
        $comment['user'] = 1;
        $comment['added'] = "123";
        $comment['avatar'] = "https://fiverr-res.cloudinary.com/image/upload/t_profile_thumb,q_auto,f_auto/v1/attachments/profile/photo/807a71236f33466dfb16e34c301cffa0-79289421564712792515/JPEG_20190802_072630_137757505.jpg";
        $comment['text'] = $comment['comment'];

    	$has_childs = !empty($comment['childs']);
        $type = ($level == 1) ? "parent" : "child";
        $is_banned = isset($comment['banned']) || 0;

        $allow_reply = true;
        if($level > 4) {
            $allow_reply = false;
        }
        ?>

        <?php ob_start(); ?>
        <div data-comment-id="<?= $comment['id'] ?>" class="comment">
            <div class="ajax comment__<?= $type ?>">
                <img class="global__image-outer-wrap global__image-outer-wrap--avatar-small" src="<?= $comment['avatar'] ?>" />
                <div id="comment_<?= $comment['id'] ?>" class="comment__summary">
                    <div class="comment__author">
                        <i class="comment__collapse-button fas fa-minus-square"></i>
                        <i class="comment__expand-button fas fa-plus-square"></i>
                        <div class="comment__username <?php echo ($is_banned ? 'comment__username--deleted' : '') ?>">
                            <?= $comment['username'] ?>
                            <?php if( $comment['class'] > 4 && !$is_banned ): ?>
                                <i class="fas fa-gavel" title="Site staff"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="<?php echo ($is_banned ? 'comment__delete-state' : 'comment__display-state') ?>">
                        <div class="comment__description markdown markdown--resize-body">
                            <p class="m-0"><?= $comment['text'] ?></p>
                        </div>
                    </div>
                    <?php if(!$is_banned && ($allow_reply && $comment['cur_class'] > 5 || $comment['cur_id'] === $comment['user'])): ?>
                        <div class="comment__edit-state is-hidden">
                            <div class="comment__description">
                                <form>
                                    <input type="hidden" name="do" value="comment_edit">
                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                    <textarea class="form-control" name="description"><?= $comment['text'] ?></textarea>
                                    <div class="align-button-container mt-2">
                                        <a href="" class="btn btn-dark mr-2 btn-sm comment__submit-button js__comment-edit-save">Save Changes</a>
                                        <div class="btn btn-secondary btn-sm comment__cancel-button js__comment-edit-cancel"><span>Cancel</span></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="comment__actions">
                        <div><?= $comment['added'] ?></div>

                        <?php if(!$is_banned && ($allow_reply && $comment['cur_class'] > 0)): ?>
                            <div class="comment__actions__button js__comment-reply">Reply</div>
                            <form>
                                <input type="hidden" name="do" value="comment_report">
                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                <div class="comment__actions__button js__comment-report">Report</div>
                            </form>
                        <?php endif; ?>

                        <?php if(!$is_banned && ($comment['cur_class'] > 5 || $comment['cur_id'] === $comment['user'])): ?>
                            <div class="comment__actions__button js__comment-edit">Edit</div>
                            <form>
                                <input type="hidden" name="do" value="comment_delete">
                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                <div class="comment__actions__button js__comment-delete">Delete</div>
                            </form>
                        <?php endif; ?>

                        <a rel="nofollow noopener" href="#comment_<?= $comment['id'] ?>" class="comment__actions__button">Permalink</a>
                    </div>
                    <div class="comment__collapse-state">
                        <div class="comment__description markdown markdown--resize-body"><p class="m-0">Comment has been collapsed.</p></div>
                    </div>
                </div>
            </div>

            <!-- Comment Children -->
            <div class="comment__children">
                <?php if( $has_childs ) { ?>

                    <?php foreach ($comment['childs'] as $key => $child) { ?>
                        
                        <?php echo $this->getCommentHtml($child, $level + 1); ?>

                    <?php } ?>

                <?php } ?>
            </div>

        </div>
        <?php
		return ob_get_clean();
    }

	public function getLinks()
    {
        return $this->toHtml();
    }

    /**
     * @param int $totalItems The total number of items.
     * @param int $itemsPerPage The number of items per page.
     * @param int $currentPage The current page number.
     * @param string $urlPattern A URL for each page, with (:num) as a placeholder for the page number. Ex. '/foo/page/(:num)'
     */
    public function createLinks($totalItems, $itemsPerPage, $currentPage, $urlPattern = '')
    {
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = $currentPage;
        $this->urlPattern = $urlPattern;

        $this->updateNumPages();
    }

    protected function updateNumPages()
    {
        $this->numPages = ($this->itemsPerPage == 0 ? 0 : (int) ceil($this->totalItems/$this->itemsPerPage));
    }

    /**
     * @param int $maxPagesToShow
     * @throws \InvalidArgumentException if $maxPagesToShow is less than 3.
     */
    public function setMaxPagesToShow($maxPagesToShow)
    {
        $this->maxPagesToShow = $maxPagesToShow;
    }

    /**
     * @return int
     */
    public function getMaxPagesToShow()
    {
        return $this->maxPagesToShow;
    }

    /**
     * @param int $currentPage
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
    }

    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * @param int $itemsPerPage
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->updateNumPages();
    }

    /**
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * @param int $totalItems
     */
    public function setTotalItems($totalItems)
    {
        $this->totalItems = $totalItems;
        $this->updateNumPages();
    }

    /**
     * @return int
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }

    /**
     * @return int
     */
    public function getNumPages()
    {
        return $this->numPages;
    }

    /**
     * @param string $urlPattern
     */
    public function setUrlPattern($urlPattern)
    {
        $this->urlPattern = $urlPattern;
    }

    /**
     * @return string
     */
    public function getUrlPattern()
    {
        return $this->urlPattern;
    }

    /**
     * @param int $pageNum
     * @return string
     */
    public function getPageUrl($pageNum)
    {
        return str_replace(self::NUM_PLACEHOLDER, $pageNum, $this->urlPattern);
    }

    public function getNextPage()
    {
        if ($this->currentPage < $this->numPages) {
            return $this->currentPage + 1;
        }

        return null;
    }

    public function getPrevPage()
    {
        if ($this->currentPage > 1) {
            return $this->currentPage - 1;
        }

        return null;
    }

    public function getNextUrl()
    {
        if (!$this->getNextPage()) {
            return null;
        }

        return $this->getPageUrl($this->getNextPage());
    }

    /**
     * @return string|null
     */
    public function getPrevUrl()
    {
        if (!$this->getPrevPage()) {
            return null;
        }

        return $this->getPageUrl($this->getPrevPage());
    }

    /**
     * Get an array of paginated page data.
     *
     * Example:
     * array(
     *     array ('num' => 1,     'url' => '/example/page/1',  'isCurrent' => false),
     *     array ('num' => '...', 'url' => NULL,               'isCurrent' => false),
     *     array ('num' => 3,     'url' => '/example/page/3',  'isCurrent' => false),
     *     array ('num' => 4,     'url' => '/example/page/4',  'isCurrent' => true ),
     *     array ('num' => 5,     'url' => '/example/page/5',  'isCurrent' => false),
     *     array ('num' => '...', 'url' => NULL,               'isCurrent' => false),
     *     array ('num' => 10,    'url' => '/example/page/10', 'isCurrent' => false),
     * )
     *
     * @return array
     */
    public function getPages()
    {
        $pages = array();

        if ($this->numPages <= 1) {
            return array();
        }

        if ($this->numPages <= $this->maxPagesToShow) {
            for ($i = 1; $i <= $this->numPages; $i++) {
                $pages[] = $this->createPage($i, $i == $this->currentPage);
            }
        } else {

            // Determine the sliding range, centered around the current page.
            $numAdjacents = (int) floor(($this->maxPagesToShow - 3) / 2);

            if ($this->currentPage + $numAdjacents > $this->numPages) {
                $slidingStart = $this->numPages - $this->maxPagesToShow + 2;
            } else {
                $slidingStart = $this->currentPage - $numAdjacents;
            }
            if ($slidingStart < 2) $slidingStart = 2;

            $slidingEnd = $slidingStart + $this->maxPagesToShow - 3;
            if ($slidingEnd >= $this->numPages) $slidingEnd = $this->numPages - 1;

            // Build the list of pages.
            $pages[] = $this->createPage(1, $this->currentPage == 1);
            if ($slidingStart > 2) {
                $pages[] = $this->createPageEllipsis();
            }
            for ($i = $slidingStart; $i <= $slidingEnd; $i++) {
                $pages[] = $this->createPage($i, $i == $this->currentPage);
            }
            if ($slidingEnd < $this->numPages - 1) {
                $pages[] = $this->createPageEllipsis();
            }
            $pages[] = $this->createPage($this->numPages, $this->currentPage == $this->numPages);
        }


        return $pages;
    }


    /**
     * Create a page data structure.
     *
     * @param int $pageNum
     * @param bool $isCurrent
     * @return Array
     */
    protected function createPage($pageNum, $isCurrent = false)
    {
        return array(
            'num' => $pageNum,
            'url' => $this->getPageUrl($pageNum),
            'isCurrent' => $isCurrent,
        );
    }

    /**
     * @return array
     */
    protected function createPageEllipsis()
    {
        return array(
            'num' => '...',
            'url' => null,
            'isCurrent' => false,
        );
    }

    /**
     * Render an HTML pagination control.
     *
     * @return string
     */
    public function toHtml()
    {
        if ($this->numPages <= 1) {
            return '';
        }

        $html = '<ul class="pagination">';
        if ($this->getPrevUrl()) {
            $html .= '<li><a href="' . htmlspecialchars($this->getPrevUrl()) . '">&laquo; '. $this->previousText .'</a></li>';
        }

        foreach ($this->getPages() as $page) {
            if ($page['url']) {
                $html .= '<li' . ($page['isCurrent'] ? ' class="active"' : '') . '><a href="' . htmlspecialchars($page['url']) . '">' . htmlspecialchars($page['num']) . '</a></li>';
            } else {
                $html .= '<li class="disabled"><span>' . htmlspecialchars($page['num']) . '</span></li>';
            }
        }

        if ($this->getNextUrl()) {
            $html .= '<li><a href="' . htmlspecialchars($this->getNextUrl()) . '">'. $this->nextText .' &raquo;</a></li>';
        }
        $html .= '</ul>';

        return $html;
    }

    public function __toString()
    {
        return $this->toHtml();
    }

    public function getCurrentPageFirstItem()
    {
        $first = ($this->currentPage - 1) * $this->itemsPerPage + 1;

        if ($first > $this->totalItems) {
            return null;
        }

        return $first;
    }

    public function getCurrentPageLastItem()
    {
        $first = $this->getCurrentPageFirstItem();
        if ($first === null) {
            return null;
        }

        $last = $first + $this->itemsPerPage - 1;
        if ($last > $this->totalItems) {
            return $this->totalItems;
        }

        return $last;
    }

    public function setPreviousText($text)
    {
        $this->previousText = $text;
        return $this;
    }

    public function setNextText($text)
    {
        $this->nextText = $text;
        return $this;
    }
}