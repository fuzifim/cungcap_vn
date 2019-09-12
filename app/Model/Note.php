<?php

namespace App\Model;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
use Elasticquent\ElasticquentTrait;
class Note extends Eloquent {
	use ElasticquentTrait;
	protected $connection = 'mongodb';
    protected $collection = 'note';
	public $timestamps = false; 
	//protected $fillable = ['type', 'created_at','updated_at','status'];
	public function author(){
        return $this->hasOne('App\Model\Note', '_id', 'user_id');
    }
	public function channel(){
        return $this->hasOne('App\Model\Note', '_id', 'channel_id');
    }
	public function toArray()
	{
		$array = parent::toArray();
		$array['id'] = $this->id;
		unset($array['_id']);
		return $array;
	}
	protected $indexSettings = [
        'analysis' => [
            'char_filter' => [
                'replace' => [
                    'type' => 'mapping',
                    'mappings' => [
                        '&=> and '
                    ],
                ],
            ],
            'filter' => [
                'word_delimiter' => [
                    'type' => 'word_delimiter',
                    'split_on_numerics' => false,
                    'split_on_case_change' => true,
                    'generate_word_parts' => true,
                    'generate_number_parts' => true,
                    'catenate_all' => true,
                    'preserve_original' => true,
                    'catenate_numbers' => true,
                ]
            ],
            'analyzer' => [
                'default' => [
                    'type' => 'custom',
                    'char_filter' => [
                        'html_strip',
                        'replace',
                    ],
                    'tokenizer' => 'whitespace',
                    'filter' => [
                        'lowercase',
                        'word_delimiter',
                    ],
                ],
            ],
        ],
    ];
	protected $mappingProperties = array(
	   'title' => array(
			'type' => 'text',
		), 
		'attribute'=>array(
			'type' => 'object', 
			'index' => false
		)
	);
}
