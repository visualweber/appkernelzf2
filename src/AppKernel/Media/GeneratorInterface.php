<?php

namespace AppKernel\Media;

use AppEntity\MediaMedia;

interface GeneratorInterface {

    /**
     * @param MediaInterface $media
     *
     * @return string
     */
    public function generatePath(MediaMedia $media);
}
