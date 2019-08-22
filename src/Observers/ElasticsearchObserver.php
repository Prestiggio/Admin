<?php

namespace Ry\Admin\Observers;

use Elasticsearch\Client;

class ElasticsearchObserver
{
    private $elasticsearch;
    
    public function __construct(Client $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }
    
    public function saved($model)
    {   
        if($model->indexable()) {
            $this->elasticsearch->index([
                'index' => $model->getSearchIndex(),
                'type' => '_doc',
                'id' => $model->id,
                'body' => $model->toSearchArray()
            ]);
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
