
<h2>Example:</h2>

<pre><code>
    $countries = array(
        'United States' => array('New York', 'Chicago', 'Atlanta', 'Dallas', 'Los Angeles', 'Denver', 'Houston', 'Las Vegas', 'Phoenix', 'Washington', 'Charlotte', 'San Francisco', 'Minneapolis', 'Seattle', 'Boston', 'Detroit', 'Orlando', 'Philadelphia', 'San Diego', 'Salt Lake City'),
        'Germany' => array('Munich', 'Dresden', 'Berlin', 'Bremen', 'Frankfurt', 'Hamburg', 'Dusseldorf', 'Dortmund', 'Stuttgart', 'Kassel', 'Westerland')
    );

    foreach ($countries as $countryName => $cities){
        CliWriter::startProgress();
        CliWriter::sendLine($countryName . ' %progress%');
        sleep(1);
        foreach ($cities as $num => $city){
            CliWriter::sendMessage($city);
            CliWriter::sendProgress($num+1, count($cities), CliWriter::PROGRESS_STYLE_OF_PERCENT);
            sleep(1);
        }
        CliWriter::endProgress();
    }
</code></pre>


![alt tag](https://raw.github.com/deeseefromcd/CliWriter/master/docs/Screenshot.png)
