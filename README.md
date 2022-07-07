# custom-search
Custom-search is a minimal and lightweight custom search engine for your own webpage based on a simple crawler scanning your webpages and indexing them. It includes wordlist-based suggestions (german version included) and correction for misspelled words.

## Indexing your website using the custom-search crawler
In order to index a page of your website call the crawler script while passing the following ```GET```-Parameters:

```s```: complete url of the page to index

```r```: recursive mode (bool): Whether to scan sites linked on the crawled site or not.

```ce```: crawl external (bool): Whether to include pages on other websites

The data gained from scanning your webpage(s) will be stored in a file called ```keywords.json``` by default.

## Including the search into your webpage
In order to include the search in your webpage you can use the script ```s.php``` as a template (you may need to make some adaptations). You can also add a form to your webpage which then passes the query as ```query``` via ```GET``` to the script in ```s.php``` which will then process the search query.
