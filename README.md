# Streamed csv export

```
use App\User;
use Incubout\Csv\Eloquent\StreamedCsvExport;

Route::get('/csv', function () {
    
    $headers = ['ID', 'Name', 'Created at'];
    $query = User::where('created_at');
    
    $stream = new StreamedCsvExport($query, function($user) {
    			return [
    				$user->id,
    				$user->name,
					$user->created_at->format('Y-m-d')
    			];
    		},
            $headers);
    
    return $stream->export('users_'.date('Ymd').'.csv');
});

```