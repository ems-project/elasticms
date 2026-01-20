[eventlistener:{{ .Env.ELASTICMS_INSTANCE_NAME }}]
command=/opt/bin/supervisord-event-listener.py /opt/sbin/ems-jobs/{{ .Env.ELASTICMS_INSTANCE_NAME }}
events=TICK_60
autorestart=false
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0