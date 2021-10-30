for f in *.aac 
do 
 g=`basename "$f" .aac`
 "C:/Program Files/ffmpeg/bin/ffmpeg.exe" -y -i ${g}.aac ${g}.mp3
done

