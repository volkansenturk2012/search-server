<?php

/*
 * This file is part of the Apisearch Server
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Apisearch\Plugin\Compression\Domain\Listener;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class ResponseListener.
 */
class ResponseListener
{
    /**
     * On kernel response.
     *
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $allowedCompressionAsString = $request
            ->headers
            ->get('Accept-Encoding');

        if (!$allowedCompressionAsString) {
            return;
        }

        $allowedCompression = explode(',', $allowedCompressionAsString);
        $allowedCompression = array_map('trim', $allowedCompression);

        if (in_array('gzip', $allowedCompression)) {
            $response->setContent(gzencode($response->getContent()));
            $response
                ->headers
                ->set('Content-Encoding', 'gzip');

            return;
        }

        if (in_array('deflate', $allowedCompression)) {
            $response->setContent(gzdeflate($response->getContent()));
            $response
                ->headers
                ->set('Content-Encoding', 'deflate');

            return;
        }
    }
}
