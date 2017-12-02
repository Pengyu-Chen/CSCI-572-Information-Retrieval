import java.io.IOException;
import java.util.HashMap;
import java.util.Iterator;
import java.util.Map;
import java.util.StringTokenizer;


import org.apache.hadoop.fs.Path;
import org.apache.hadoop.io.LongWritable;
import org.apache.hadoop.io.Text;
import org.apache.hadoop.mapreduce.Job;
import org.apache.hadoop.mapreduce.Mapper;
import org.apache.hadoop.mapreduce.Reducer;

import org.apache.hadoop.mapreduce.lib.input.FileInputFormat;
import org.apache.hadoop.mapreduce.lib.output.FileOutputFormat;



public class WordCount {
	
	public static class WordCountMapper 
	extends Mapper<LongWritable, Text, Text, Text> {
		
		
		private Text word = new Text();
		
		public void map(LongWritable key, Text value, Context context)
			throws IOException, InterruptedException{
			String line=value.toString();
			StringTokenizer tokenizer=new StringTokenizer(line);
			Text ID = null;
			ID = new Text(tokenizer.nextToken());
			
			while(tokenizer.hasMoreTokens()) {
				word.set(tokenizer.nextToken());
				context.write(word, ID);
			};
			
		}
		
	}
	
	
	public static class WordCountReducer 
	    extends Reducer<Text, Text, Text, Text> {

		public void reduce(Text key, Iterable<Text> values, Context context)
			throws IOException, InterruptedException{
			Map<String,Integer> index=new HashMap<String,Integer>();
			
			for (Text value: values) {
				String ID=value.toString();
				if (index.containsKey(ID))
					index.put(ID,index.get(ID)+1);
				else 
					index.put(ID, 1);
				}
			Iterator<Map.Entry<String, Integer>> it = index.entrySet().iterator();
			
			String result="";
			while(it.hasNext()) {
				Map.Entry<String, Integer> entry = it.next();
				if(it.hasNext())
					result+=entry.getKey()+":"+entry.getValue()+" ";
				else
					result+=entry.getKey()+":"+entry.getValue();
			}
			
			context.write(key, new Text(result));
		}
	}
	
	
	public static void main(String[] args) 
		// TODO Auto-generated method stub
		throws IOException, ClassNotFoundException, InterruptedException {
		if (args.length!=2) {
			System.err.println("Usage: Word Count <input path><output path>");
			System.exit(-1);
		}	
		
		Job job= new Job();
		
		job.setJarByClass(WordCount.class);
		job.setJobName("Word Count");
		
		FileInputFormat.addInputPath(job, new Path(args[0]));
		FileOutputFormat.setOutputPath(job, new Path(args[1]));
		
		job.setMapperClass(WordCountMapper.class);
		job.setReducerClass(WordCountReducer.class);
		
		job.setOutputKeyClass(Text.class);
		job.setOutputValueClass(Text.class);
		job.waitForCompletion(true);
		
		
		}

	

}
