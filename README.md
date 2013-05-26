php-lite-solr
=============

JSON-only PHP simple solr client that is up-to-date and works for solr 4.2

Features
--------

1. uses the new [JSON interface](http://wiki.apache.org/solr/UpdateJSON)
2. uses PHP's builtin associative arrays
3. reuses cUrl connection for best performance
4. trivial, small and simple (maps directly to solr documentation)
5. includes some shortcuts like commit and optimize

Usage
-----

    require_once('LiteSolr.php');
    $solr=new LiteSolr('http://localhost:8983/solr/core0/'); // this will automatically ping it
    // although no need to do ping, you can do it like this
    $solr->ping();
    // to index documents, see http://wiki.apache.org/solr/UpdateJSON
    $docs=array();
    $docs[]=array('id'=>'d10', 'title'=>'Ali Ahmad', 'description'=>'Ali autobio');
    $solr->update($docs, array('commit'=>'true')); // 
    // Realtime-get feature, see http://wiki.apache.org/solr/RealTimeGet
    $doc=$solr->get(array('id'=>'d10')); 
    // query, see http://wiki.apache.org/solr/CommonQueryParameters and http://www.solrtutorial.com/solr-query-syntax.html
    $r=$solr->select(array('q'=>'*:*'));
    var_dump($r['response']['docs']);
    // below some shortcuts on update
    $solr->delete(array("id"=>"d10"));
    $solr->delete(array('query'=>'*', 'commitWithin'=>1000)); // commit within 1 second
    $solr->commit();
    $solr->optimize(array("waitSearcher"=>false));

