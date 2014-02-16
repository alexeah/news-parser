<?php

namespace Np\NewsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * Render all news
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $isUserAdmin = $this->get('security.context')->isGranted('ROLE_ADMIN');

        $feedItems = $this->getDoctrine()->getRepository('NpNewsBundle:FeedItem')->findBy(array(), array('publishTime' => 'desc'));

        return $this->render('NpNewsBundle:Default:index.html.twig', array(
            'feed_items' => self::mapFeedItems($feedItems),
            'is_user_admin' => $isUserAdmin,
        ));
    }

    /**
     * Check out remote news and add it if fresh
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function pullAction()
    {
        $this->getDoctrine()->getRepository('NpNewsBundle:Feed')->pull();
        $feedItems = $this->getDoctrine()->getRepository('NpNewsBundle:FeedItem')->findBy(array(), array('publishTime' => 'desc'));
        return $this->render('NpNewsBundle:Default:index.html.twig', array(
            'feed_items' => self::mapFeedItems($feedItems),
            'is_user_admin' => true,
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
            $formattedItem['publish_time'] = $item->getPublishTime();
            $formattedItem['title'] = $item->getTitle();
            $formattedItem['description'] = $item->getDescription();
            $formattedItem['url'] = $item->getUrl();
            $formattedItems[] = $formattedItem;
        }
        return $formattedItems;
    }
}