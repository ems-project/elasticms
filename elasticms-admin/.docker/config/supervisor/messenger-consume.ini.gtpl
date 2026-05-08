[program:{{ .Env.ELASTICMS_INSTANCE_NAME }}-messenger-consume]
command=/opt/sbin/{{ .Env.ELASTICMS_INSTANCE_NAME }}-messenger-consume-exec
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
numprocs={{ .Env.MESSENGER_CONSUME_SUPERVISOR_NUMPROCS }}
startsecs=0
autorestart=true
startretries=10
stopwaitsecs=20
process_name=%(program_name)s_%(process_num)02d
