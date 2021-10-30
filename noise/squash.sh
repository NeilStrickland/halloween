for f in scream grunt ghost ghost2 feet evil crickets chains boogie
do
 ./ffmpeg.exe -i ${f}.mp3 -ab 48k ${f}_small.mp3
 mv ${f}.mp3 big
 mv ${f}_small.mp3 ${f}.mp3
done


