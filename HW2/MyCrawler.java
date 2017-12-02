

import java.io.FileWriter;
import java.io.IOException;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;
import java.util.regex.Pattern;

import edu.uci.ics.crawler4j.crawler.Page;
import edu.uci.ics.crawler4j.crawler.WebCrawler;
import edu.uci.ics.crawler4j.parser.HtmlParseData;
import edu.uci.ics.crawler4j.url.WebURL;

public class MyCrawler extends WebCrawler {
	static int objcounter = 0;

	private final static Pattern FILTERS = Pattern.compile(".*(\\.(css|js"
			+ "|mp3|mp3|zip|gz))$");

	static FileWriter fetchFile = null;
	static FileWriter visitFile = null;
	static FileWriter urlsFile = null;

	static int totalOutgoingUrls = 0;
	static int uniqueUrls = 0;
	static int uniqueUrlsInside = 0;
	static int uniqueUrlsOutside = 0;
	static int totalSites = 0;
	static int totalSuccess = 0;
	static int totalFail = 0;

	static Set<String> urlset = new HashSet<>();
	static Map<Integer, Integer> statusCodesCountMap = new HashMap<Integer, Integer>();
	static Map<String, Integer> contentTypes = new HashMap<String,Integer>();

	static int level1 = 0, level2 = 0, level3 = 0, level4 = 0, level5 = 0;
	String prefix = "https://www.nydailynews.com/";
	String prefix1 = "http://www.nydailynews.com/";


	static {
		try {
			fetchFile = new FileWriter("fetch_New_York_Daily_News.csv");

			fetchFile.append("URL");
			fetchFile.append(",");
			fetchFile.append("status code");
			fetchFile.append("\n");

			visitFile = new FileWriter("visit_New_York_Daily_News.csv");
			visitFile.append("URL");
			visitFile.append(",");
			visitFile.append("size");
			visitFile.append(",");
			visitFile.append("outgoing links");
			visitFile.append(",");
			visitFile.append("content-type");
			visitFile.append("\n");

			urlsFile = new FileWriter("urls_nytimes.csv");
			urlsFile.append("URL");
			urlsFile.append(",");
			urlsFile.append("resides or not");
			urlsFile.append("\n");
		} catch (IOException e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}

	public void uniqueURLstats(WebURL url) {
		if (urlset.contains(url.getURL()) == false) {
			urlset.add(url.getURL());
			uniqueUrls++;
			if (url.getURL().startsWith(prefix)
					|| url.getURL().startsWith(prefix1))
				uniqueUrlsInside++;
			else
				uniqueUrlsOutside++;
		}
	}

	/**
	 * This method receives two parameters. The first parameter is the page in
	 * which we have discovered this new url and the second parameter is the new
	 * url. You should implement this function to specify whether the given url
	 * should be crawled or not (based on your crawling logic). In this example,
	 * we are instructing the crawler to ignore urls that have css, js, git, ...
	 * extensions and to only accept urls that start with
	 * "http://www.viterbi.usc.edu/". In this case, we didn't need the
	 * referringPage parameter to make the decision.
	 */

	@Override
	public boolean shouldVisit(Page referringPage, WebURL url) {
		synchronized (MyCrawler.class) {
			try {
				uniqueURLstats(url);

				urlsFile.append(url.getURL().replace(',', '-'));
				urlsFile.append(",");

				if (url.getURL().startsWith(prefix)
						|| url.getURL().startsWith(prefix1))
					urlsFile.append("OK");
				else
					urlsFile.append("N_OK");
				urlsFile.append("\n");

				String href = url.getURL().toLowerCase();
				return !FILTERS.matcher(href).matches()
						&& (href.startsWith(prefix) || href.startsWith(prefix1));
			} catch (Exception e) {
				e.printStackTrace();
				return false;
			}
		}
	}

	public void captureFileSize(long size) {
		int KB = 1024;
		if (size < KB)
			level1++;
		else if (size >= KB && size < (KB * 10))
			level2++;
		else if (size >= (KB * 10) && size < (KB * 100))
			level3++;
		else if (size >= (KB * 100) && size < (KB * 1000))
			level4++;
		else
			level5++;
	}

	/**
	 * This function is called when a page is fetched and ready to be processed
	 * by your program.
	 */
	@Override
	public void visit(Page page) {
		synchronized (MyCrawler.class) {
			try {
				String url = page.getWebURL().getURL();
				System.out.println("URL: " + url);
				int outgoinglinks = 0;
				visitFile.append(url.replace(',', '-'));
				visitFile.append(",");
				visitFile.append("" + page.getContentData().length);
				visitFile.append(",");

				if (page.getParseData() instanceof HtmlParseData) {
					HtmlParseData htmlParseData = (HtmlParseData) page.getParseData();
					Set<WebURL> links = htmlParseData.getOutgoingUrls();
					// System.out.println("Number of outgoing links: " +
					// links.size());
					outgoinglinks += links.size();
				}

				visitFile.append("" + outgoinglinks);
				visitFile.append(",");

				visitFile.append(page.getContentType());
				visitFile.append("\n");

				captureFileSize(page.getContentData().length);
				totalOutgoingUrls += outgoinglinks;
				if (contentTypes.containsKey(page.getContentType())) 
					contentTypes.put(page.getContentType(),contentTypes.get(page.getContentType())+1);
					
				else
					contentTypes.put(page.getContentType(), 1);
			} catch (Exception e) {
				e.printStackTrace();
			}
		}
	}

	public void statusCodeStatistics(int statusCode) {
		totalSites += 1;

		if (statusCode >= 200 && statusCode < 300) {
			totalSuccess += 1;
		} else {
			totalFail += 1;
		}

		if (statusCodesCountMap.containsKey(statusCode))
			statusCodesCountMap.put(statusCode,statusCodesCountMap.get(statusCode) + 1);
		else
			statusCodesCountMap.put(statusCode, 1);
	}

	@Override
	protected void handlePageStatusCode(WebURL webUrl, int statusCode,
			String statusDescription) {
		synchronized (MyCrawler.class) {
			statusCodeStatistics(statusCode);
			try {
				fetchFile.append(webUrl.getURL().replace(',', '-'));
				fetchFile.append(",");
				fetchFile.append("" + statusCode);
				fetchFile.append("\n");

			} catch (IOException e) {
				e.printStackTrace();
			}
			super.handlePageStatusCode(webUrl, statusCode, statusDescription);
		}
	}

	public void printstatictics() {
		System.out.println("# fetches attempted: " + totalSites);
		System.out.println("# fetches succeeded: " + totalSuccess);
		System.out.println("# fetches aborted or failed: " + totalFail);

		System.out.println("Total URLs extracted: " + totalOutgoingUrls);
		System.out.println("# unique URLs extracted: " + uniqueUrls);
		System.out.println("# unique URLs within News Site: " + uniqueUrlsInside);
		System.out.println("# unique URLs outside News Site: "
				+ uniqueUrlsOutside);

		System.out.println("Status Codes: ");
		for (Integer key : statusCodesCountMap.keySet()) {
			System.out.println(key + " " + statusCodesCountMap.get(key));
		}

		System.out.println("< 1KB: " + level1);
		System.out.println("1KB-10KB: " + level2);
		System.out.println("10KB-100KB: " + level3);
		System.out.println("100KB - 1MB: " + level4);
		System.out.println(">=1MB: " + level5);

		System.out.println("Content types: ");
		for (String key : contentTypes.keySet()) {
			System.out.println(key+" "+contentTypes.get(key));
		}
	}

	@Override
	public void onBeforeExit() {
		synchronized (MyCrawler.class) {
			try {
				objcounter++;
				if (objcounter == 7) {
					fetchFile.close();
					visitFile.close();
					urlsFile.close();
					printstatictics();
				}
			} catch (Exception e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
		}
	}
}