import { showError, showSuccess } from '@nextcloud/dialogs'

export default {
	data() {
		return {
			copied: false,
			copyLoading: false,
			copySuccess: false,
		}
	},

	methods: {
		async copyToClipboard(string) {
			// change to loading status
			this.copyLoading = true

			// copy content to clipboard
			try {
				navigator.clipboard.writeText(string)
				this.copySuccess = true
				this.copied = true

				// Notify success
				showSuccess(t('pandoc', 'Copied to the clipboard.'))
			} catch (error) {
				this.copySuccess = false
				this.copied = true
				showError(t('pandoc', 'Could not copy to the clipboard'))
			} finally {
				this.copyLoading = false
				setTimeout(() => {
					// stop loading status regardless of outcome
					this.copied = false
					this.copySuccess = false
				}, 2000)
			}
		},
	},
}
