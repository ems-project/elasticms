import ProgressBar from '../../helpers/progressBar'

export default class JobPoller {
    async run(jobId: string, progressBar: ProgressBar) {
        return Promise.allSettled([this.#start(jobId), this.#poll(jobId, progressBar)])
    }

    async #start(jobId: string) {
        const response = await fetch(`/job/start/${jobId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        })
        return response.json()
    }

    async #poll(jobId: string, progressBar: ProgressBar): Promise<any> {
        const jobStatus = await this.#status(jobId)

        if (jobStatus.started && jobStatus.progress > 0) {
            progressBar.status('Running ...').progress(jobStatus.progress).style('success')
        }
        if (jobStatus.done === true) {
            progressBar.status('Finished').progress(100)
            return jobStatus
        }

        await new Promise((resolve) => setTimeout(resolve, 1500))
        return await this.#poll(jobId, progressBar)
    }

    async #status(jobId: string) {
        const response = await fetch(`/job/status/${jobId}`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        return response.json()
    }
}
