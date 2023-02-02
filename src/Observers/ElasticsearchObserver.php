<?php

namespace Ry\Admin\Observers;

use Elasticsearch\Client;

class ElasticsearchObserver
{
    private $elasticsearch;
    
    public function __construct()
    {
        $this->elasticsearch = app(Client::class);
    }
    
    public function saved($model)
    {   
        if($model->indexable()) {
            $docs = $model->toSearchArray();
            foreach($docs as $doc) {
                $this->elasticsearch->index([
                    'index' => $model->getSearchIndex(),
                    'type' => '_doc',
                    'id' => $model->id,
                    'body' => $doc
                ]);
            }
        }
    }
    
    public function deleted($model)
    {
        $this->elasticsearch->delete([
            'index' => $model->getSearchIndex(),
            'type' => '_doc',
            'id' => $model->id,
        ]);
    }
}
