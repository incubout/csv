<?php

namespace Incubout\Csv\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamedCsvExport {
    
    protected $query;
    protected $headers;
    protected $callback;
    
    protected $separator = ';';
    protected $enclosure = '"';
    
    public function __construct(Builder $query, callable $callback, array $headers = null)
    {
        $this->query = $query;
        $this->callback = $callback;
        $this->headers = is_array($headers) ? $headers : [];
    }
    
    public function export($filename)
    {
        $response = new StreamedResponse(function() {
            // Open output stream
            $handler = fopen('php://output', 'w');
            fputs($handler, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // Add CSV headers
            if (!empty($this->headers)) {
                $this->addLine($handler, $this->headers);
            }    
            
            // Loop over products
			$this->query->chunk(1000, function($items) use ($handler) {
				foreach ($items as $item) {
                    $line = call_user_func_array($this->callback, [$item]);
					$this->addLine($handler, $line);
				}
			});

			// Close the output stream
			fclose($handler);
		}, 200, [
			'Content-Type' => 'text/csv',
			'Content-Disposition' => 'attachment; filename="'.$filename.'"',
		]);
        
		return $response;
    }
    
    protected function addLine($handler, array $line)
	{
        $separator = $this->separator;
        $enclosure = $this->enclosure;
        
		// Scape the enclosure from the values
		$line = array_map(function($value) use ($enclosure) {
			return str_replace($enclosure, $enclosure.$enclosure, $value);
		}, $line);
		
		fputs($handler, $enclosure.implode($enclosure.$separator.$enclosure, $line).$enclosure.PHP_EOL);
	}
    
}