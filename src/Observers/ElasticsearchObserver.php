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
            try {
                $this->elasticsearch->index([
                    'index' => $model->getSearchIndex(),
                    'type' => '_doc',
                    'id' => $model->id,
                    'body' => $model->toSearchArray()
                ]);
            }
            catch(\Exception $e) {

            }
        }
    }
    
    public function deleted($model)
    {
        try {
            $this->elasticsearch->delete([
                'index' => $model->getSearchIndex(),
                'type' => '_doc',
                'id' => $model->id,
            ]);
        }
        catch(\Exception $e) {
            
        }
    }
}
