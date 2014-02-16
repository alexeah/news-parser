<?php

namespace Np\NewsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * Time between pulls (seconds)
     */
    const PULL_INTERVAL = 100;

    /**
     * Render all news
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $this->quietPullIfNewsDeprecated();
        $isUserAdmin = $this->get('security.context')->isGranted('ROLE_ADMIN');

        $feedItems = $this->getDoctrine()->getRepository('NpNewsBundle:FeedItem')->findBy(array(), array('publishTime' => 'desc'));

        return $this->render('NpNewsBundle:Default:index.html.twig', array(
            'feed_items' => self::mapFeedItems($feedItems),
            'is_user_admin' => $isUserAdmin,
            'message' => null,
        ));
    }

    /**
     * Check out remote news and add it if fresh
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pullAction()
    {
        $amountAdded = $this->getDoctrine()->getRepository('NpNewsBundle:Feed')->pull();
        $feedItems = $this->getDoctrine()->getRepository('NpNewsBundle:FeedItem')->findBy(array(), array('publishTime' => 'desc'));
        $message = sprintf('%d items were added', $amountAdded);
        return $this->render('NpNewsBundle:Default:index.html.twig', array(
            'feed_items' => self::mapFeedItems($feedItems),
            'is_user_admin' => true,
            'message' => $message,
        ));
    }

    /**
     * Prepare news to be displayed
     *
     * @param array $feedItems
     * @return array
     */
    static private function mapFeedItems(array $feedItems)
    {
        $formattedItems = array();
        foreach ($feedItems as $item) {
            $formattedItem = array();
            $formattedItem['publish_time'] = date('d M, Y H:i:s', $item->getPublishTime());
            $formattedItem['title'] = $item->getTitle();
            $formattedItem['description'] = $item->getDescription();
            $formattedItem['url'] = $item->getUrl();
            $formattedItems[] = $formattedItem;
        }
        return $formattedItems;
    }

    /**
     * Update feed items if it is time
     */
    private function quietPullIfNewsDeprecated()
    {
        $feed = $this->getDoctrine()->getRepository('NpNewsBundle:Feed')->findBy(array(), array('lastPullTime' => 'desc'), 1);
        if (!count($feed)) {
            return;
        }
        $feed = current($feed);
        if ($_SERVER['REQUEST_TIME'] - $feed->getLastPullTime() > self::PULL_INTERVAL) {
            $this->getDoctrine()->getRepository('NpNewsBundle:Feed')->pull();
        }
    }
}