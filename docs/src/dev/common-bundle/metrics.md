# Metrics

Metrics can be enabled for all our applications, as this is a common feature.

By defining the following environment variables, a new route /metrics will be registered. This route
is only accessible from localhost on port 8881.

When accessed, it returns Prometheus metrics by invoking all registered collector services.

```bash
EMS_METRIC_ENABLED=true
EMS_METRIC_HOST=localhost
EMS_METRIC_PORT=8881
```

If you want to manually collect and/or clear the metrics we can run the following command:

```bash
php bin/console ems:metric:collect --clear
```

## Collectors

In our bundles, we use collector objects. Each collector defines a validUntil timestamp to improve
performance and avoid querying the database too frequently.

### [EMS Info](https://github.com/ems-project/elasticms/blob/HEAD/EMS/common-bundle/src/Common/Metric/EmsInfoMetricCollector.php)

This collector is registered by the common bundle with a validity period of 1 day. It parses the
composer.json file and return the versions for elasticms packages.

```bash
# HELP ems_info Info ems versions
# TYPE ems_info gauge
ems_info{client="6.3.1",common="6.3.1",core="6.3.1",form="6.3.1",submission="6.3.1",symfony="v6.4.20"} 1
```

### [Form submissions](https://github.com/ems-project/elasticms/blob/HEAD/EMS/submission-bundle/src/Metric/SubmissionMetricCollector.php)

This collector is registered by the Submission Bundle with a validity period of 5 minutes. It
queries the form submission table and collects the following information:

- Total number of form submissions
- Total number of unprocessed submissions
- Total number of submission errors

```bash
# HELP emss_submissions_total Total form submissions
# TYPE emss_submissions_total gauge
emss_submissions_total{form_instance="default",form_name="User group form"} 5

# HELP emss_submissions_unprocessed_total Total unprocessed submissions
# TYPE emss_submissions_unprocessed_total gauge
emss_submissions_unprocessed_total{form_instance="default",form_name="User group form"} 5

# HELP emss_submissions_errors_total Total count error submissions
# TYPE emss_submissions_errors_total gauge
emss_submissions_errors_total{form_instance="default",form_name="User group form"} 4
```

### [Job](https://github.com/ems-project/elasticms/blob/HEAD/EMS/core-bundle/src/Core/Metric/JobMetricCollector.php)

This collector is registered by the core bundle with a validity period of 5 minutes. In queries the
job table and collects the following information ( grouped by tag):

- Timestamp of the last created job
- Timestamp of the last modified job
- Count jobs
- Count jobs done
- Count jobs started

```text
# HELP emsco_job_last_created Timestamp of the last created job
# TYPE emsco_job_last_created gauge
emsco_job_last_created{tag=""} 1738332985
emsco_job_last_created{tag="custom_worker"} 1747812950

# HELP emsco_job_last_modified Timestamp of the last modified job
# TYPE emsco_job_last_modified gauge
emsco_job_last_modified{tag=""} 1738332986
emsco_job_last_modified{tag="custom_worker"} 1747812992

# HELP emsco_job_count_jobs Count jobs
# TYPE emsco_job_count_jobs gauge
emsco_job_count_jobs{tag=""} 1
emsco_job_count_jobs{tag="custom_worker"} 3

# HELP emsco_job_count_jobs_done Count jobs done
# TYPE emsco_job_count_jobs_done gauge
emsco_job_count_jobs_done{tag=""} 1
emsco_job_count_jobs_done{tag="custom_worker"} 3

# HELP emsco_job_count_jobs_started Count jobs started
# TYPE emsco_job_count_jobs_started gauge
emsco_job_count_jobs_started{tag=""} 1
emsco_job_count_jobs_started{tag="custom_worker"} 3
```

The core jobService also registers a gauge metric when the next-job is requested. This allows
monitoring of the last time a next-job request was made. This metric is recorded in real time and is
not subject to the collectors' 5-minute validity constraint.

```text
# HELP emsco_job_last_ping Timestamp of the last ping
# TYPE emsco_job_last_ping gauge
emsco_job_last_ping{tag=""} 1747819578
emsco_job_last_ping{tag="lookup_worker"} 1747819483
```
