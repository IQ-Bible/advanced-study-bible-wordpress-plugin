// Automatically display a loading dialog on any AJAX request
// -------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
  const originalOpen = XMLHttpRequest.prototype.open
  const originalSend = XMLHttpRequest.prototype.send

  // Reset counter and clear any existing dialogs on page load
  let activeRequests = 0
  let loaderTimeout

  // Immediately close any open dialog on page load/refresh
  closeLoadingDialog()

  function showLoaderWithDelay () {
    // Clear any existing timeout first
    clearTimeout(loaderTimeout)
    loaderTimeout = setTimeout(showLoadingDialog, 1000)
  }

  function showLoadingDialog () {
    // Only show if we still have active requests
    if (activeRequests > 0) {
      const loadingDialog = document.getElementById('loading-dialog')
      if (loadingDialog) {
        loadingDialog.showModal()
      }
    }
  }

  function closeLoadingDialog () {
    clearTimeout(loaderTimeout) // Clear any pending loader timeout
    const loadingDialog = document.getElementById('loading-dialog')
    if (loadingDialog) {
      try {
        loadingDialog.close()
      } catch (e) {
        // Ignore errors if dialog wasn't open
      }
    }
  }

  XMLHttpRequest.prototype.open = function (method, url, async) {
    if (url) {
      activeRequests++
      showLoaderWithDelay()
    }
    return originalOpen.apply(this, arguments)
  }

  XMLHttpRequest.prototype.send = function () {
    const xhr = this

    xhr.addEventListener('readystatechange', function () {
      if (xhr.readyState === 4) {
        // Request completed
        activeRequests = Math.max(0, activeRequests - 1) // Prevent negative
        if (activeRequests <= 0) {
          closeLoadingDialog()
        }
      }
    })

    xhr.addEventListener('error', function () {
      activeRequests = Math.max(0, activeRequests - 1) // Prevent negative
      if (activeRequests <= 0) {
        closeLoadingDialog()
      }
    })

    return originalSend.apply(this, arguments)
  }
})

/**
 * Shows a message in the custom dialog.
 * @param {string} message The text message to display.
 * @param {string} type 'success', 'error', or 'info' (default) for styling.
 * @param {number} autoCloseDelay Milliseconds to auto-close after opening (0 or null/undefined for manual close).
 */
function showMessageDialog (message, type = 'info', autoCloseDelay = 0) {
  const dialog = document.getElementById('iqbible-message-dialog')
  const messageTextElement = document.getElementById('iqbible-message-text')
  // Get the inner content div for styling
  const contentArea = dialog
    ? dialog.querySelector('.iqbible-message-dialog-content')
    : null

  if (!dialog || !messageTextElement || !contentArea) {
    const errorMsg =
      typeof iqbible_ajax !== 'undefined' &&
      iqbible_ajax.i18n &&
      iqbible_ajax.i18n.errorDialogMissing
        ? iqbible_ajax.i18n.errorDialogMissing
        : 'Message dialog elements not found. Falling back to alert.'
    console.error(errorMsg)

    alert(message) // Fallback if dialog elements are missing
    return
  }

  // Set message text using textContent to prevent HTML injection
  messageTextElement.textContent = message

  // Remove previous type classes and add the new one for styling
  contentArea.classList.remove(
    'iqbible-message-success',
    'iqbible-message-error',
    'iqbible-message-info'
  )
  if (type === 'success') {
    contentArea.classList.add('iqbible-message-success')
  } else if (type === 'error') {
    contentArea.classList.add('iqbible-message-error')
  } else {
    // Default to info style
    contentArea.classList.add('iqbible-message-info')
  }

  // Ensure dialog is not already open before showing
  if (!dialog.open) {
    try {
      dialog.showModal()
    } catch (e) {
      const errorMsg =
        typeof iqbible_ajax !== 'undefined' &&
        iqbible_ajax.i18n &&
        iqbible_ajax.i18n.errorDialogShow
          ? iqbible_ajax.i18n.errorDialogShow
          : 'Error showing message dialog:' // Add 'errorDialogShow' key
      console.error(errorMsg, e)
      alert(message) // Fallback
    }
  }

  // Auto-close logic (if specified)
  if (autoCloseDelay && autoCloseDelay > 0) {
    setTimeout(() => {
      // Check if the dialog is still open before trying to close
      if (dialog.open) {
        try {
          dialog.close()
        } catch (e) {
          // Ignore errors if dialog already closed somehow
        }
      }
    }, autoCloseDelay)
  }
}

// Global VersionId
// ------------------
var versionId = iqbible_ajax.versionId || 'kjv'

// Scroll an element into view based on its ID
// ---------------------------------------------
function scrollToElementById (elementId, verseId = null) {
  // Find the element by ID
  const element = document.getElementById(elementId)
  if (element) {
    // Scroll the element into view with a smooth behavior
    element.scrollIntoView({
      behavior: 'smooth'
    })
    // Only add highlight if verseId is provided
    if (verseId) {
      // Add the highlight class to the element
      element.classList.add('highlighted')
      // Optionally remove the highlight after a certain time
      setTimeout(() => {
        element.classList.remove('highlighted')
      }, 3000)
    }
  }
}

// Update the URL parameters
// ------------------------------
function updateURL (bookId, chapterId, version, verseId = null) {
  let newUrl = `${window.location.pathname}?bookId=${encodeURIComponent(
    bookId
  )}&chapterId=${encodeURIComponent(chapterId)}&versionId=${encodeURIComponent(
    version.toLowerCase()
  )}`

  // Append verseId if provided
  if (verseId) {
    newUrl += `&verseId=${encodeURIComponent(verseId)}`
  }

  window.history.pushState({ path: newUrl }, '', newUrl)
}

// Load Chapter Content
// ------------------------
function loadChapterContent (
  bookId,
  chapterId,
  version,
  verseId = null,
  language = null
) {


  console.log('loadChapterContent called with:', { bookId, chapterId, version, verseId });

  // Update the URL parameters without reloading the page
  updateURL(bookId, chapterId, version, verseId)

  var xhr = new XMLHttpRequest()
  xhr.open('POST', iqbible_ajax.ajaxurl, true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

  xhr.onload = function () {
    if (xhr.status === 200) {
      var response = JSON.parse(xhr.responseText)

      // Update Prev/Next Button State based on response
      const currentChapterNum = parseInt(chapterId, 10) // Use chapterId passed to function
      const totalChapters = parseInt(response.totalChapters, 10) || 0 // Get total from response
      const prevBtn = document.getElementById('prev-chapter')
      const nextBtn = document.getElementById('next-chapter')

      if (prevBtn) {
        prevBtn.classList.toggle('disabled', currentChapterNum <= 1)
      }
      if (nextBtn) {
        // Disable if total is known AND current is last or beyond
        nextBtn.classList.toggle(
          'disabled',
          totalChapters > 0 && currentChapterNum >= totalChapters
        )
      }

      // Populate the chapter content
      document.getElementById('iqbible-chapter-results').innerHTML =
        response.chapterContent

      // Map Bible Book Icon images for alt versions
      const bookIdToIconNameMap = {
        '01': 'genesis',
        '02': 'exodus',
        '03': 'leviticus',
        '04': 'numbers',
        '05': 'deuteronomy',
        '06': 'joshua',
        '07': 'judges',
        '08': 'ruth',
        '09': '1-samuel',
        10: '2-samuel',
        11: '1-kings',
        12: '2-kings',
        13: '1-chronicles',
        14: '2-chronicles',
        15: 'ezra',
        16: 'nehemiah',
        17: 'esther',
        18: 'job',
        19: 'psalms',
        20: 'proverbs',
        21: 'ecclesiastes',
        22: 'song-of-solomon',
        23: 'isaiah',
        24: 'jeremiah',
        25: 'lamentations',
        26: 'ezekiel',
        27: 'daniel',
        28: 'hosea',
        29: 'joel',
        30: 'amos',
        31: 'obadiah',
        32: 'jonah',
        33: 'micah',
        34: 'nahum',
        35: 'habakkuk',
        36: 'zephaniah',
        37: 'haggai',
        38: 'zechariah',
        39: 'malachi',
        40: 'matthew',
        41: 'mark',
        42: 'luke',
        43: 'john',
        44: 'acts',
        45: 'romans',
        46: '1-corinthians',
        47: '2-corinthians',
        48: 'galatians',
        49: 'ephesians',
        50: 'philippians',
        51: 'colossians',
        52: '1-thessalonians',
        53: '2-thessalonians',
        54: '1-timothy',
        55: '2-timothy',
        56: 'titus',
        57: 'philemon',
        58: 'hebrews',
        59: 'james',
        60: '1-peter',
        61: '2-peter',
        62: '1-john',
        63: '2-john',
        64: '3-john',
        65: 'jude',
        66: 'revelation'
      }

      const headerElement = document.getElementById('fetch-books-header')
      const bookName = response.bookName
      const chapterNum = parseInt(chapterId, 10)
      const currentBookIdPadded = String(bookId).padStart(2, '0')

      if (headerElement && bookName && iqbible_ajax.iconBaseUrl) {
        const iconNameBase = bookIdToIconNameMap[currentBookIdPadded]
        const iconUrl = iqbible_ajax.iconBaseUrl + iconNameBase + '.png'
        headerElement.innerHTML = ''
        const img = document.createElement('img')
        img.src = iconUrl
        img.alt = bookName
        img.style.marginRight = '8px'
        img.style.verticalAlign = 'middle'
        img.style.height = '2em'
        img.onerror = function () {
          console.warn('Bible icon not found:', iconUrl)
          // Fallback includes book name
          headerElement.textContent = bookName + ' ' + chapterNum
          this.remove()
        }

        const bookNameTextNode = document.createTextNode(bookName)

        const chapterTextNode = document.createTextNode(' ' + chapterNum)

        headerElement.appendChild(img)
        headerElement.appendChild(bookNameTextNode)
        headerElement.appendChild(chapterTextNode)
      } else {
        // Fallback or error logging
        if (headerElement) {
          headerElement.textContent = (bookName || 'Book') + ' ' + chapterNum // Fallback includes book name
        }
        if (!headerElement)
          console.error('Element "#fetch-books-header" not found.')
        if (!bookName) console.error('Book name missing in response.')
        if (!iqbible_ajax.iconBaseUrl)
          console.error('iconBaseUrl missing in iqbible_ajax object.')
      }

      document.getElementById('fetch-books-header-version').innerText =
        ' (' + version.toUpperCase() + ')'

      // Set text direction based on version
      const mainContent = document.getElementById(
        'iqbible-bible-content-wrapper'
      )
      if (version.toLowerCase() === 'svd') {
        mainContent.dir = 'rtl'
      } else {
        mainContent.dir = 'ltr'
      }

      // If verseId is provided, scroll to that verse after content loaded, else, just scroll to iqbible-main
      if (verseId) {
        setTimeout(() => scrollToElementById(verseId, 'verseId' + verseId), 300)
      } else {
        // Scroll to main content area
        scrollToElementById('iqbible-main')
      }

      // Check for audio narration
      checkAudioNarration(bookId, chapterId, version)
    } else {
      const errorMsg =
        typeof iqbible_ajax !== 'undefined' &&
        iqbible_ajax.i18n &&
        iqbible_ajax.i18n.errorFetchChapter
          ? iqbible_ajax.i18n.errorFetchChapter
          : 'An error occurred while retrieving the chapter:' // Add 'errorFetchChapter' key to PHP localize
      document.getElementById('iqbible-chapter-results').innerHTML =
        '<p>' + errorMsg + ' ' + xhr.status + '</p>'
    }
  }

  // Send AJAX request to load chapter with version and optional verse
  xhr.send(
    'action=iq_bible_chapter_ajax_handler&bookId=' +
      encodeURIComponent(bookId) +
      '&chapterId=' +
      encodeURIComponent(chapterId) +
      '&versionId=' +
      encodeURIComponent(version.toLowerCase()) +
      (verseId ? '&verseId=' + encodeURIComponent(verseId) : '') +
      '&security=' +
      encodeURIComponent(iqbible_ajax.nonce) // <-- ADDED NONCE
  )
}

// Helper function to check audio narration
// --------------------------------------------
function checkAudioNarration (bookId, chapterId, version) {
  // Clear any existing audio player
  document.getElementById('iqbible-audio-player').innerHTML = ''

  var xhrAudio = new XMLHttpRequest()
  xhrAudio.open('POST', iqbible_ajax.ajaxurl, true)
  xhrAudio.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

  xhrAudio.onload = function () {
    if (xhrAudio.status === 200) {
      var audioResponse = JSON.parse(xhrAudio.responseText)
      if (
        audioResponse.success &&
        audioResponse.data &&
        audioResponse.data.audioUrl
      ) {
        const noAudioSupportMsg =
          typeof iqbible_ajax !== 'undefined' &&
          iqbible_ajax.i18n &&
          iqbible_ajax.i18n.noAudioSupport
            ? iqbible_ajax.i18n.noAudioSupport
            : 'Your browser does not support the audio element.' // Add 'noAudioSupport' key to PHP localize
        var audioPlayer = `<audio class='iqbible-audio' id='iqbible-audio-player-element' controls>
                    <source src="${audioResponse.data.audioUrl}" type="audio/mpeg">
                    ${noAudioSupportMsg}
                </audio>`

        document.getElementById('iqbible-audio-player').innerHTML = audioPlayer
      }
    }
  }

  xhrAudio.send(
    'action=iq_bible_audio_check&bookId=' +
      encodeURIComponent(bookId) +
      '&chapterId=' +
      encodeURIComponent(chapterId) +
      '&versionId=' +
      encodeURIComponent(version.toLowerCase()) +
      '&security=' +
      encodeURIComponent(iqbible_ajax.nonce)
  )
}

// Initialize with the first tab
// ------------------------------------
document.addEventListener('DOMContentLoaded', function () {
  openTab('bible')
})

// Function to open the selected tab
// ----------------------------
function openTab (tabId) {
  // Hide all tab contents
  var contents = document.querySelectorAll('.tab-content')
  contents.forEach(function (content) {
    content.style.display = 'none' // Hide content
    content.classList.remove('active') // Remove active class
  })

  // Remove 'active' class from all tab buttons
  var buttons = document.querySelectorAll('.iqbible-tab-button')
  buttons.forEach(function (button) {
    button.classList.remove('active')
  })

  // Show the selected tab content
  var selectedContent = document.getElementById(tabId)
  if (selectedContent) {
    selectedContent.style.display = 'block' // Show selected content
    selectedContent.classList.add('active') // Add active class
  }

  // Add 'active' class to the selected tab button
  var activeButton = document.querySelector(
    '.iqbible-tab-button[onclick="openTab(\'' + tabId + '\')"]'
  )
  if (activeButton) {
    activeButton.classList.add('active')
  }

  // Focus the input field if the search tab is opened
  if (tabId === 'search') {
    setTimeout(function () {
      var queryInput = document.getElementById('query')
      if (queryInput) {
        queryInput.focus() // Focus the input
      }
    }, 100) // Delay to ensure the element is visible
  }
}

// Reading Plans
// ------------------
document.addEventListener('DOMContentLoaded', function () {
  document
    .getElementById('iqbible-reading-plan-form')
    .addEventListener('submit', function (e) {
      e.preventDefault() // Prevent the form from submitting normally

      // Get the form values
      var days = document.getElementById('iqbible-days').value
      var customDays = document.getElementById('iqbible-customDays').value // Get the custom days value
      var requestedStartDate =
        document.getElementById('iqbible-startDate').value
      var sections = document.getElementById('iqbible-sections').value
      var requestedAge = document.getElementById('iqbible-age').value
      var planName = document.getElementById('iqbible-planName').value // Get the plan name

      // Validate custom days if custom is selected
      if (days === 'custom') {
        if (!customDays || isNaN(customDays) || customDays <= 0) {
          showMessageDialog(iqbible_ajax.i18n.enterValidDays, 'error')
          return
        }
        days = customDays // Use the custom number of days provided by the user
      }

      // Show loading indicator
      var contentDiv = document.getElementById('iqbible-reading-plan-content')

      const loadingMsg =
        typeof iqbible_ajax !== 'undefined' &&
        iqbible_ajax.i18n &&
        iqbible_ajax.i18n.loading
          ? iqbible_ajax.i18n.loading
          : 'Loading plan...'
      contentDiv.innerHTML =
        '<div class="loading-indicator">' + loadingMsg + '</div>'

      // Perform the AJAX request
      var xhr = new XMLHttpRequest()
      xhr.open('POST', iqbible_ajax.ajaxurl, true) // Use the localized ajaxurl
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
      xhr.onload = function () {
        if (xhr.status === 200) {
          // Parse the JSON response
          try {
            var response = JSON.parse(xhr.responseText)

            if (response.success && response.data && response.data.html) {
              // Set the HTML content from the response
              contentDiv.innerHTML = response.data.html

              // Add event listeners to the reading plan links
              contentDiv
                .querySelectorAll('.reading-plan-link')
                .forEach(function (link) {
                  link.addEventListener('click', function (e) {
                    e.preventDefault()

                    // Retrieve the bookId and chapterId from the clicked link's data attributes
                    currentBookId = this.getAttribute('data-book-id')
                    currentChapterId = this.getAttribute('data-chapter-id')
                    loadChapterContent(
                      currentBookId,
                      currentChapterId,
                      versionId
                    )

                    openTab('bible')
                  })
                })

              // Add event listener to the print button
              contentDiv
                .querySelector('#print-reading-plan-btn')
                .addEventListener('click', function () {
                  // If 'save as PDF', ensure filename is planName (document title)
                  const originalTitle = document.title
                  let printTitle = planName

                  const restoreTitle = () => {
                    document.title = originalTitle
                    window.removeEventListener('afterprint', restoreTitle)
                  }

                  // Create a print-specific stylesheet
                  document.title = printTitle
                  var printStyleSheet = document.createElement('style')
                  printStyleSheet.type = 'text/css'
                  printStyleSheet.innerHTML = `
                    @media print {
                      body * { visibility: hidden; }
                      #printable-plan-content, #printable-plan-content * { visibility: visible; }
                      #printable-plan-content { position: absolute; left: 0; top: 0; width: 100%; }
                      #print-reading-plan-btn, .iqbible-print-plan-action { display: none !important; }
                      h2 { margin-bottom: 10px; font-size: 16pt; }
                      h3 { margin-top: 20px; font-size: 14pt; }
                      li { margin-bottom: 8px; page-break-inside: avoid; }
                      hr { border: 0; border-top: 1px solid #ccc; margin: 5px 0; }
                      a { text-decoration: none; color: black; }
                   
                      p, span, li, strong, label { font-size: 10pt; }
                    }
                  `
                  document.head.appendChild(printStyleSheet)

                  if (window.onafterprint !== undefined) {
                    window.addEventListener('afterprint', restoreTitle, {
                      once: true
                    })
                  } else {
                    setTimeout(restoreTitle, 1000)
                  }

                  window.print()

                  setTimeout(function () {
                    if (document.head.contains(printStyleSheet)) {
                      document.head.removeChild(printStyleSheet)
                    }
                  }, 1500)

                  // Remove the print stylesheet after a delay
                  setTimeout(function () {
                    document.head.removeChild(printStyleSheet)
                  }, 1000)
                })

              // Scroll to the plan details section
              var planDetails = document.getElementById('plan-details')
              if (planDetails) {
                planDetails.scrollIntoView({ behavior: 'smooth' })
              }
            } else if (response.data && response.data.message) {
              contentDiv.innerHTML =
                '<div class="error-message">' + response.data.message + '</div>'
            } else {
              const errorMsg =
                typeof iqbible_ajax !== 'undefined' &&
                iqbible_ajax.i18n &&
                iqbible_ajax.i18n.errorGeneratingPlan
                  ? iqbible_ajax.i18n.errorGeneratingPlan
                  : 'Failed to generate reading plan.' // Reuse key
              contentDiv.innerHTML =
                '<div class="error-message">' + errorMsg + '</div>'
            }
          } catch (e) {
            console.error('Error parsing JSON response:', e)
            const errorMsg =
              typeof iqbible_ajax !== 'undefined' &&
              iqbible_ajax.i18n &&
              iqbible_ajax.i18n.errorProcessingResponse
                ? iqbible_ajax.i18n.errorProcessingResponse
                : 'Error processing the response from server.'
            contentDiv.innerHTML =
              '<p class="error-message">' + errorMsg + '</p>'
          }
        } else {
          const errorMsg =
            response.data.message ||
            (typeof iqbible_ajax !== 'undefined' &&
            iqbible_ajax.i18n &&
            iqbible_ajax.i18n.errorGeneratingPlan
              ? iqbible_ajax.i18n.errorGeneratingPlan
              : 'An error occurred generating the plan.')
          contentDiv.innerHTML =
            '<div class="error-message">' + errorMsg + '</div>'
        }
      }

      xhr.onerror = function () {
        const errorMsg =
          typeof iqbible_ajax !== 'undefined' &&
          iqbible_ajax.i18n &&
          iqbible_ajax.i18n.networkError
            ? iqbible_ajax.i18n.networkError
            : 'Network error occurred. Please check your connection.'
        contentDiv.innerHTML = '<p class="error-message">' + errorMsg + '</p>'
      }

      // Send the form data as URL-encoded parameters
      xhr.send(
        'action=iq_bible_plans' +
          '&days=' +
          encodeURIComponent(days) +
          '&requestedStartDate=' +
          encodeURIComponent(requestedStartDate) +
          '&sections=' +
          encodeURIComponent(sections) +
          '&requestedAge=' +
          encodeURIComponent(requestedAge) +
          '&iqbible-planName=' +
          encodeURIComponent(planName) +
          '&security=' +
          encodeURIComponent(iqbible_ajax.nonce)
      )
    })

  // Show/Hide custom days input based on selection
  document
    .getElementById('iqbible-days')
    .addEventListener('change', function () {
      var customDaysInput = document.getElementById('iqbible-customDays')
      if (this.value === 'custom') {
        customDaysInput.style.display = 'block'
      } else {
        customDaysInput.style.display = 'none'
        customDaysInput.value = '' // Clear custom days input when not in use
      }
    })
})

// Search
// --------------

// Handle form submission
document
  .getElementById('iqbible-search-form')
  .addEventListener('submit', function (e) {
    e.preventDefault()

    // Get the search query
    var query = document.getElementById('iqbible-query').value

    // Perform the AJAX request
    var xhr = new XMLHttpRequest()
    xhr.open('POST', iqbible_ajax.ajaxurl, true)
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
    xhr.onload = function () {
      if (xhr.status === 200) {
        // Display the results
        document.getElementById('iqbible-search-results').innerHTML =
          xhr.responseText

        // Add click handlers to all search results
        attachSearchResultHandlers()
      } else {
        const errorMsg =
          typeof iqbible_ajax !== 'undefined' &&
          iqbible_ajax.i18n &&
          iqbible_ajax.i18n.errorSearch
            ? iqbible_ajax.i18n.errorSearch
            : 'An error occurred during the search...' // Add 'errorSearch' key to PHP localize
        document.getElementById('iqbible-search-results').innerHTML =
          '<p>' + errorMsg + '</p>'
      }
    }
    xhr.send(
      'action=iq_bible_search&query=' +
        encodeURIComponent(query) +
        '&versionId=' +
        encodeURIComponent(versionId) +
        '&security=' +
        encodeURIComponent(iqbible_ajax.nonce) // <-- ADDED NONCE
    )
  })

// Function to attach handlers to search results
function attachSearchResultHandlers () {
  document.querySelectorAll('.bible-search-result').forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault()

      // Get data from the clicked link
      currentBookId = this.dataset.bookId
      currentChapterId = this.dataset.chapterId
      const verseId = this.dataset.verseId
      const versionId = this.dataset.versionId

      // Switch to the Bible tab
      openTab('bible')

      // Load the chapter content
      loadChapterContent(currentBookId, currentChapterId, versionId, verseId)
    })
  })
}

// Dictionary
// --------------
document.addEventListener('DOMContentLoaded', function () {
  document
    .getElementById('iqbible-dictionary-form')
    .addEventListener('submit', function (e) {
      e.preventDefault()

      var query = document.getElementById('iqbible-definition-query').value

      var xhr = new XMLHttpRequest()
      xhr.open('POST', iqbible_ajax.ajaxurl, true)
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
      xhr.onload = function () {
        if (xhr.status === 200) {
          document.getElementById('iqbible-definition-results').innerHTML =
            xhr.responseText
        } else {
          const errorMsg =
            typeof iqbible_ajax !== 'undefined' &&
            iqbible_ajax.i18n &&
            iqbible_ajax.i18n.errorDictionary
              ? iqbible_ajax.i18n.errorDictionary
              : 'An error occurred during the definition retrieval:' // Add 'errorDictionary' key to PHP localize
          document.getElementById('iqbible-definition-results').innerHTML =
            '<p>' + errorMsg + ' ' + xhr.status + '</p>'
        }
      }
      xhr.send(
        'action=iq_bible_define&iqbible-definition-query=' +
          encodeURIComponent(query) +
          '&security=' +
          encodeURIComponent(iqbible_ajax.nonce) // <-- ADDED NONCE
      )
    })
})

// Strong's
// --------------
document.addEventListener('DOMContentLoaded', function () {
  document
    .getElementById('iqbible-strongs-form')
    .addEventListener('submit', function (e) {
      e.preventDefault()

      var query = document.getElementById('iqbible-strongs-query').value // Correct input ID
      var lexicon = query.charAt(0) // Extract first letter (H or G)
      var id = query.slice(1) // Get the rest of the string

      var xhr = new XMLHttpRequest()
      xhr.open('POST', iqbible_ajax.ajaxurl, true)
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
      xhr.onload = function () {
        if (xhr.status === 200) {
          document.getElementById('iqbible-strongs-results').innerHTML =
            xhr.responseText
        } else {
          const errorMsg =
            typeof iqbible_ajax !== 'undefined' &&
            iqbible_ajax.i18n &&
            iqbible_ajax.i18n.errorStrongs
              ? iqbible_ajax.i18n.errorStrongs
              : 'An error occurred during the concordance retrieval:' // Add 'errorStrongs' key to PHP localize
          document.getElementById('iqbible-strongs-results').innerHTML =
            '<p>' + errorMsg + ' ' + xhr.status + '</p>'
        }
      }
      // Use the correct action name for Strong's
      xhr.send(
        'action=iq_bible_strongs_ajax_handler&lexicon=' +
          encodeURIComponent(lexicon) +
          '&id=' +
          encodeURIComponent(id) +
          '&security=' +
          encodeURIComponent(iqbible_ajax.nonce) // <-- ADDED NONCE
      ) // Send lexicon and id
    })
})

// Cross references
// --------------------
function showCrossReferences (verseId) {
  var xhr = new XMLHttpRequest()
  xhr.open('POST', iqbible_ajax.ajaxurl, true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
  xhr.onload = function () {
    if (xhr.status === 200) {
      var crossRefsContainer = document.getElementById('cross-references')
      crossRefsContainer.innerHTML = xhr.responseText

      // Add click handlers to the cross reference links
      crossRefsContainer
        .querySelectorAll('.cross-reference-link')
        .forEach(function (link) {
          link.addEventListener('click', function (e) {
            e.preventDefault()

            // Get data attributes
            const bookId = this.getAttribute('data-book-id')
            const chapterId = this.getAttribute('data-chapter-id')
            const verseId = this.getAttribute('data-verse-id')

            // Close the dialog
            document.getElementById('cross-references-dialog').close()

            // Load the new chapter content
            loadChapterContent(bookId, chapterId, versionId, 'verse-' + verseId)
          })
        })

      // Open the dialog
      document.getElementById('cross-references-dialog').showModal()
    } else {
      showMessageDialog(
        iqbible_ajax.i18n.errorFetchCrossRefs + ' ' + xhr.status,
        'error'
      )
    }
  }
  xhr.send(
    'action=iq_bible_get_cross_references&verseId=' +
      encodeURIComponent(verseId) +
      '&security=' +
      encodeURIComponent(iqbible_ajax.nonce)
  )
}

// Original Text
// ----------------
function showOriginalText (verseId) {
  var xhr = new XMLHttpRequest()
  xhr.open('POST', iqbible_ajax.ajaxurl, true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
  xhr.onload = function () {
    if (xhr.status === 200) {
      document.getElementById('original-text').innerHTML = xhr.responseText // Use innerHTML
      document.getElementById('original-text-dialog').showModal() // Show the modal
    } else {
      showMessageDialog(
        iqbible_ajax.i18n.errorFetchOriginalText + ' ' + xhr.status,
        'error'
      )
    }
  }
  xhr.send(
    'action=iq_bible_get_original_text&verseId=' +
      encodeURIComponent(verseId) +
      '&security=' +
      encodeURIComponent(iqbible_ajax.nonce)
  )
}

// Copy verse
// ------------------
function copyVerse (
  verseId,
  bookName,
  chapterNumber,
  versionId,
  siteName,
  language
) {
  // Find the verse container
  var verseElement = document.getElementById('verse-' + verseId)

  // Get the copyable-text span content only
  var copyableSpan = verseElement.querySelector('.copyable-text')
  var verseText = copyableSpan ? copyableSpan.textContent : ''

  // Get the verse number from the <sup> tag
  var verseNumber = verseElement.querySelector('sup')
    ? verseElement.querySelector('sup').textContent
    : ''

  // Clean up the text
  verseText = verseText.replace(/\s+/g, ' ').trim()

  // Format the reference
  var reference = ` - ${bookName} ${chapterNumber}:${verseNumber} (${versionId.toUpperCase()}) - ${siteName}`
  var fullText = verseText + reference

  // Create temporary textarea to copy
  var textarea = document.createElement('textarea')
  textarea.value = fullText

  // Set direction attribute based on language
  const textDirection = language; // The 6th parameter is now 'ltr' or 'rtl'

  if (textDirection === 'rtl') { // Check if the direction passed is 'rtl'
    textarea.setAttribute('dir', 'rtl');
  } else {
    textarea.setAttribute('dir', 'ltr'); // Default to LTR otherwise
  }

  document.body.appendChild(textarea)

  // Select and copy
  textarea.select()
  document.execCommand('copy')

  // Remove textarea
  document.body.removeChild(textarea)

  // Update message
  var messageDiv = document.getElementById('verse-message-' + verseId)
  if (messageDiv) {
    showMessageDialog(iqbible_ajax.i18n.verseCopied, 'success', 3000)
  }
}

// Bible Chapter AJAX w/ book, chapter selections
// ------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
  let lastOpenBookId = null // Variable to track the last open book

  // Function to get URL parameters
  function getURLParam (paramName) {
    const urlParams = new URLSearchParams(window.location.search)
    return urlParams.get(paramName)
  }

  // Check if URL has bookId, chapterId, and
  // versionId parameters; if not, use defaults:
  let currentBookId = getURLParam('bookId') || '1' // Default to Genesis (bookId 1)
  let currentChapterId = getURLParam('chapterId') || '1' // Default to Chapter 1
  let selectedVersionName = versionId

  // GetBookInfo
  // --------------
  document
    .getElementById('fetch-books-header-intro')
    .addEventListener('click', function () {
      var dialog = document.getElementById('book-intro-dialog')
      var content = document.getElementById('book-intro-content')

      // Show loading text while fetching

      const loadingMsg =
        typeof iqbible_ajax !== 'undefined' &&
        iqbible_ajax.i18n &&
        iqbible_ajax.i18n.loading
          ? iqbible_ajax.i18n.loading
          : 'Loading...'
      content.innerHTML = '<p>' + loadingMsg + '</p>'

      dialog.showModal()

      // AJAX request to fetch the book introduction
      var xhr = new XMLHttpRequest()
      xhr.open('POST', iqbible_ajax.ajaxurl, true)
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
      xhr.onload = function () {
        if (xhr.status === 200) {
          // Set the dialog content
          content.innerHTML = xhr.responseText

          // Dynamically add the close button
          const closeButton = document.createElement('span')
          closeButton.classList.add('iqbible-dialog-close')

          closeButton.textContent = '×'
          const closeLabel =
            typeof iqbible_ajax !== 'undefined' &&
            iqbible_ajax.i18n &&
            iqbible_ajax.i18n.close
              ? iqbible_ajax.i18n.close
              : 'Close'
          closeButton.setAttribute('aria-label', closeLabel)
          closeButton.setAttribute('title', closeLabel)

          closeButton.style.cursor = 'pointer'

          // Add click event to close the dialog
          closeButton.addEventListener('click', function () {
            dialog.close()
          })

          // Prepend the close button to the dialog content
          content.prepend(closeButton)
        } else {
          const errorMsg =
            typeof iqbible_ajax !== 'undefined' &&
            iqbible_ajax.i18n &&
            iqbible_ajax.i18n.errorBookIntro
              ? iqbible_ajax.i18n.errorBookIntro
              : 'Error loading book introduction. Please try again.'
          content.innerHTML = '<p>' + errorMsg + '</p>'
        }
      }

      xhr.send(
        'action=iq_bible_book_intro&bookId=' +
          encodeURIComponent(currentBookId) +
          '&security=' +
          encodeURIComponent(iqbible_ajax.nonce)
      )
    })

  // Story click
  // ---------------
  document.querySelectorAll('.story-link').forEach(function (storyLink) {
    storyLink.addEventListener('click', function (e) {
      // Retrieve the bookId, chapterId, and verseId from the clicked link's data attributes
      currentBookId = this.getAttribute('data-book-id')
      currentChapterId = this.getAttribute('data-chapter-id')
      const verseId = this.getAttribute('data-verse-id')
      // Trigger the reload of the Bible chapter content with the correct params
      loadChapterContent(
        currentBookId,
        currentChapterId,
        versionId,
        'story-' + verseId
      )
      // Switch to the Bible tab and make it active
      openTab('bible') // Call the function to switch to the Bible tab
    })
  })

  // Function to get the chapter count for a specific book
  // --------------------------------------------------------
  function getChapterCount (bookId, callback) {
    var xhrChapterCount = new XMLHttpRequest()
    xhrChapterCount.open('POST', iqbible_ajax.ajaxurl, true)
    xhrChapterCount.setRequestHeader(
      'Content-Type',
      'application/x-www-form-urlencoded'
    )

    xhrChapterCount.onload = function () {
      if (xhrChapterCount.status === 200) {
        var response = JSON.parse(xhrChapterCount.responseText)
        if (response.chapterCount) {
          // Check for chapterCount directly

          callback(response.chapterCount)
        }
      }
    }

    xhrChapterCount.send(
      'action=iq_bible_chapter_count_ajax_handler&bookId=' +
        bookId +
        '&security=' +
        encodeURIComponent(iqbible_ajax.nonce)
    )
  }

  // Handle prev chapter click
  // -----------------------------
  document
    .getElementById('prev-chapter')
    .addEventListener('click', function () {
      // Read currentBookId and currentChapterId from the URL each time the button is clicked
      let currentBookId = getURLParameter('bookId')
      let currentChapterId = parseInt(getURLParameter('chapterId'))

      if (currentChapterId > 1) {
        // Decrease chapter ID for the previous chapter
        currentChapterId -= 1

        // Reload content for the previous chapter
        loadChapterContent(currentBookId, currentChapterId, selectedVersionName)

        // Optionally update URL for continuity
        updateURL(currentBookId, currentChapterId, selectedVersionName)
      }
    })

  // Handle next chapter click
  // -----------------------------
  document
    .getElementById('next-chapter')
    .addEventListener('click', function () {
      // Read currentBookId and currentChapterId from the URL each time the button is clicked
      let currentBookId = getURLParameter('bookId')
      let currentChapterId = parseInt(getURLParameter('chapterId'))

      getChapterCount(currentBookId, function (chapterCount) {
        if (currentChapterId < chapterCount) {
          // Increase chapter ID for the next chapter
          currentChapterId += 1

          // Reload content for the next chapter
          loadChapterContent(
            currentBookId,
            currentChapterId,
            selectedVersionName
          )

          // Optionally update URL for continuity
          updateURL(currentBookId, currentChapterId, selectedVersionName)
        }
      })
    })

  // Topics
  // ----------------------------

  document
    .querySelectorAll('.iqbible-topic-item')
    .forEach(function (topicItem) {
      topicItem.addEventListener('click', function () {
        var topic = this.dataset.topic
        var topicElement = this

        var xhr = new XMLHttpRequest()
        xhr.open('POST', iqbible_ajax.ajaxurl, true)
        xhr.setRequestHeader(
          'Content-Type',
          'application/x-www-form-urlencoded'
        )
        xhr.onload = function () {
          if (xhr.status === 200) {
            // Insert the response directly under the clicked topic
            var topicResultsDiv = document.createElement('div')
            topicResultsDiv.classList.add('topic-results')
            topicResultsDiv.innerHTML = xhr.responseText

            // Remove any existing results under this topic
            var existingResults = topicElement.nextElementSibling
            if (
              existingResults &&
              existingResults.classList.contains('topic-results')
            ) {
              existingResults.remove()
            }

            // Insert the new results after the clicked topic
            topicElement.parentNode.insertBefore(
              topicResultsDiv,
              topicElement.nextSibling
            )

            // Add click handlers to the newly created verse links
            initializeTopicVerseLinks(topicResultsDiv)
          }
        }
        xhr.send(
          'action=iq_bible_topics_ajax_handler&topic=' +
            encodeURIComponent(topic) +
            '&security=' +
            encodeURIComponent(iqbible_ajax.nonce)
        )
      })
    })

  // Function to initialize verse link click handlers
  // --------------------------------------------------
  function initializeTopicVerseLinks (container) {
    container.querySelectorAll('.topic-verse-link').forEach(function (link) {
      link.addEventListener('click', function (e) {
        e.preventDefault()

        // Get data attributes
        const bookId = this.getAttribute('data-book-id')
        const chapterId = this.getAttribute('data-chapter-id')
        const verseId = this.getAttribute('data-verse-id')

        // Load the chapter content
        loadChapterContent(bookId, chapterId, versionId, 'verse-' + verseId)

        // Switch to Bible tab
        openTab('bible')
      })
    })
  }

  // Function to get URL parameters
  // ---------------------------------
  function getURLParameter (name) {
    const urlParams = new URLSearchParams(window.location.search)
    return urlParams.get(name)
  }

  // Load chapter on first page load
  // --------------------------------
  const verseId = getURLParameter('verseId') // Get the verseId from URL
  // Call function to load chapter content
  loadChapterContent(
    currentBookId,
    currentChapterId,
    selectedVersionName,
    verseId
  )

  // Open Book Dialog when the "Fetch Books" header is clicked
  // -----------------------------------------------------------
  document
    .getElementById('fetch-books-header')
    .addEventListener('click', function () {
      var xhr = new XMLHttpRequest()
      xhr.open('POST', iqbible_ajax.ajaxurl, true)
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

      xhr.onload = function () {
        if (xhr.status === 200) {
          // Insert the HTML response with books into the dialog and show it
          document.getElementById('books-list').innerHTML = xhr.responseText
          document.getElementById('book-dialog').showModal()

          // Reopen the last opened chapter dropdown if it exists
          if (lastOpenBookId) {
            openChapterDropdown(lastOpenBookId)
          }
        }
      }

      // Send AJAX request to get the list of books
      xhr.send(
        'action=iq_bible_books_ajax_handler&security=' +
          encodeURIComponent(iqbible_ajax.nonce)
      )
    })

  // Handle clicks on book items and generate chapter buttons
  // ----------------------------------------------------------
  document.getElementById('books-list').addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('book-item')) {
      var bookId = e.target.getAttribute('data-book-id')
      var bookCategory = e.target.getAttribute('data-book-category')

      var xhr = new XMLHttpRequest()
      xhr.open('POST', iqbible_ajax.ajaxurl, true)
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

      xhr.onload = function () {
        if (xhr.status === 200) {
          var response = JSON.parse(xhr.responseText)
          var chapterCount = response.chapterCount

          // Remove any existing chapter dropdowns
          document.querySelectorAll('.chapter-dropdown').forEach(function (el) {
            el.remove()
          })

          // Create a container for the chapter buttons
          var chapterContainer = document.createElement('div')
          chapterContainer.classList.add('chapter-dropdown')

          // Generate chapter buttons and group them in rows of 5
          var row
          for (var i = 1; i <= chapterCount; i++) {
            if ((i - 1) % 5 === 0) {
              row = document.createElement('div')
              row.classList.add('iqbible-chapter-row') // Add a class to style the row
              chapterContainer.appendChild(row)
            }

            var button = document.createElement('button')
            button.classList.add('iqbible-chapter-item')
            button.setAttribute('data-chapter-id', i)
            button.setAttribute('data-book-id', bookId)
            button.textContent = i // Display chapter number as button text

            currentBookId = bookId // Update current book

            // Add event listener to load the chapter content when clicked
            // Add event listener to load the chapter content when clicked
            button.addEventListener('click', function () {
              var chapterId = this.getAttribute('data-chapter-id')

              // Ensure global variables are updated
              currentBookId = bookId // Set the correct current book ID globally
              currentChapterId = chapterId // Set the correct current chapter ID globally

              // Reload content for the selected chapter
              clearUrlParams()
              loadChapterContent(bookId, chapterId, selectedVersionName)

              // Close the book dialog after selecting the chapter
              document.getElementById('book-dialog').close()
            })

            row.appendChild(button)
          }

          // Find the book-item element and append the chapter list under it
          var bookItem = document.querySelector(
            '.book-item[data-book-id="' + bookId + '"]'
          )
          if (bookItem) {
            bookItem.insertAdjacentElement('afterend', chapterContainer)
          }

          // Track the last opened book dropdown
          lastOpenBookId = bookId
        }
      }

      // Send AJAX request with bookId and bookCategory
      xhr.send(
        'action=iq_bible_chapter_count_ajax_handler&bookId=' +
          encodeURIComponent(bookId) +
          '&bookCategory=' +
          encodeURIComponent(bookCategory) +
          '&security=' +
          encodeURIComponent(iqbible_ajax.nonce)
      )
    }
  })

  // Predefined array of versions that have audio available:
  // --------------------------------------------------------
  const audioAvailableVersions = ['kjv', 'svd', 'rv1909']

  // Open the Versions Dialog when the version text is clicked
  // -----------------------------------------------------------
  document
    .getElementById('fetch-books-header-version')
    .addEventListener('click', function () {
      var xhr = new XMLHttpRequest()
      xhr.open('POST', iqbible_ajax.ajaxurl, true)
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

      xhr.onload = function () {
        if (xhr.status === 200) {
          var response = JSON.parse(xhr.responseText)
          var versionsDialog = document.getElementById('versions-dialog')
          var dialogContent = versionsDialog.querySelector(
            '.iqbible-dialog-content'
          )

          // Clear existing content
          const title =
            typeof iqbible_ajax !== 'undefined' &&
            iqbible_ajax.i18n &&
            iqbible_ajax.i18n.selectVersion
              ? iqbible_ajax.i18n.selectVersion
              : 'Select a Version' // Add 'selectVersion' key to PHP localize
          dialogContent.innerHTML =
            '<span class="iqbible-dialog-close" onclick="document.getElementById(\'versions-dialog\').close()">×</span><h2>' +
            title +
            '</h2>'

          // Group versions by language
          var groupedVersions = {}
          response.forEach(function (version) {
            var language =
              version.language.charAt(0).toUpperCase() +
              version.language.slice(1)
            if (!groupedVersions[language]) {
              groupedVersions[language] = []
            }
            groupedVersions[language].push(version)
          })

          // Generate HTML for each language group
          for (var language in groupedVersions) {
            // Create language heading
            var languageHeading = document.createElement('h3')
            languageHeading.textContent = language
            dialogContent.appendChild(languageHeading)

            // Create a list to hold the versions
            var versionsList = document.createElement('ul')
            versionsList.classList.add('iqbible-versions-list')

            // Populate the list with versions under this language
            groupedVersions[language].forEach(function (version) {
              var listItem = document.createElement('li')
              var versionName = version.abbreviation.toLowerCase()

              // Default display text for the version
              var displayText = version.abbreviation + ' - ' + version.version

              // Check if audio is available for this version
              if (audioAvailableVersions.includes(versionName)) {
                const audioText =
                  typeof iqbible_ajax !== 'undefined' &&
                  iqbible_ajax.i18n &&
                  iqbible_ajax.i18n.withAudio
                    ? iqbible_ajax.i18n.withAudio
                    : ' - with AUDIO NARRATION' // Add 'withAudio' key to PHP localize
                if (audioAvailableVersions.includes(versionName)) {
                  displayText += audioText
                }
              }

              listItem.textContent = displayText
              listItem.setAttribute('data-version-id', version.version_id)
              listItem.setAttribute('data-version-name', versionName)
              listItem.setAttribute('data-version-language', language)
              listItem.classList.add('iqbible-version-item')

              // Add event listener for selecting a version
              listItem.addEventListener('click', function () {
                selectedVersionName = this.getAttribute('data-version-name')
                versionId = selectedVersionName

                selectedLanguage = this.getAttribute(
                  'data-version-language'
                ).toLowerCase()

                // Clear the old book cache and update the language,
                // then load the new chapter content.
                updateLanguageAndClearCache(selectedLanguage).then(() => {
                  loadChapterContent(
                    currentBookId,
                    currentChapterId,
                    selectedVersionName,
                    null
                  )
                }).catch(error => {
                    console.error("Failed to update language:", error);
                });




                // Close the versions dialog
                versionsDialog.close()
              })

              // Append the list item to the list
              versionsList.appendChild(listItem)
            })

            // Append the list to the dialog content
            dialogContent.appendChild(versionsList)
          }

          // Show the dialog with version options
          versionsDialog.showModal()
        }
      }

      // Send AJAX request to get versions
      xhr.send(
        'action=iq_bible_get_versions&security=' +
          encodeURIComponent(iqbible_ajax.nonce)
      )
    })

  // Function to open the chapter dropdown of a specific book
  function openChapterDropdown (bookId) {
    var bookItem = document.querySelector(
      '.book-item[data-book-id="' + bookId + '"]'
    )
    if (bookItem) {
      bookItem.click() // Simulate clicking the book to reopen the chapter dropdown
    }
  }
})


/**
 * Updates the language preference and clears the old book cache via AJAX.
 * @param {string} language The new language to set.
 * @returns {Promise} A promise that resolves on success and rejects on failure.
 */
function updateLanguageAndClearCache (language) {
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest()
    xhr.open('POST', iqbible_ajax.ajaxurl, true)
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

    xhr.onload = function () {
      if (xhr.status === 200) {
        try {
          const response = JSON.parse(xhr.responseText)
          if (response.success) {
            resolve() // Resolve the Promise on success
          } else {
            reject(new Error(response.data.message || 'Unknown error')) // Reject on failure
          }
        } catch (e) {
          reject(new Error('Invalid JSON response from server.'))
        }
      } else {
        reject(new Error(`AJAX request failed with status: ${xhr.status}`)) // Reject on HTTP error
      }
    }

    xhr.onerror = function () {
      reject(new Error('Network error occurred.')) // Reject on network error
    }

    xhr.send(
      'action=iq_bible_update_language_and_clear_cache&language=' +
        encodeURIComponent(language) +
        '&security=' +
        encodeURIComponent(iqbible_ajax.nonce)
    )
  })
}















// Notes
// --------------
document.addEventListener('DOMContentLoaded', function () {
  var currentNoteId = null
  var saveNoteBtn = document.getElementById('save-note-btn')
  var cancelNoteBtn = document.getElementById('cancel-note-btn')

  // Load the saved notes on page load if user logged in
  if (iqbible_ajax.isUserLoggedIn == '1') {
    loadSavedNotes()
  }

  function saveNote () {
    var noteContent = tinymce.get('iqbible_editor').getContent()

    if (!noteContent.trim()) {
      showMessageDialog(iqbible_ajax.i18n.noteNotEmpty, 'error')

      return
    }

    var action = currentNoteId ? 'iq_bible_update_note' : 'iq_bible_save_note'
    var params = new FormData()
    params.append('action', action)
    params.append('note_text', noteContent)

    if (currentNoteId) {
      params.append('note_id', currentNoteId)
    }

    var xhr = new XMLHttpRequest()
    xhr.open('POST', iqbible_ajax.ajaxurl, true)

    xhr.onload = function () {
      if (xhr.status === 200) {
        showMessageDialog(iqbible_ajax.i18n.noteSaved, 'success', 3000)

        var response = JSON.parse(xhr.responseText)

        if (response.success) {
          loadSavedNotes()
          resetEditor()
        } else {
          showMessageDialog(
            iqbible_ajax.i18n.errorSavingNote + ' ' + response.error,
            'error'
          )
        }
      }
    }
    params.append('security', iqbible_ajax.nonce)
    xhr.send(params)
  }

  // Reset the notes editor
  // ---------------------
  function resetEditor () {
    tinymce.get('iqbible_editor').setContent('')
    currentNoteId = null

    const saveNewNoteText =
      typeof iqbible_ajax !== 'undefined' &&
      iqbible_ajax.i18n &&
      iqbible_ajax.i18n.saveNewNote
        ? iqbible_ajax.i18n.saveNewNote
        : 'Save New Note'
    saveNoteBtn.textContent = saveNewNoteText

    cancelNoteBtn.style.display = 'none'
  }

  // Cancel edit
  // ---------------------
  function cancelEdit () {
    resetEditor()
  }

  if (iqbible_ajax.isUserLoggedIn == '1') {
    // is logged in
    saveNoteBtn.addEventListener('click', saveNote)
    cancelNoteBtn.addEventListener('click', cancelEdit)
  }

  // Load Saved Notes
  // ---------------------
  function loadSavedNotes () {
    var xhr = new XMLHttpRequest()
    xhr.open('POST', iqbible_ajax.ajaxurl, true)
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

    xhr.onload = function () {
      if (xhr.status === 200) {
        var response = JSON.parse(xhr.responseText)
        if (response.success) {
          var notesList = document.getElementById('iqbible-notes-list')
          notesList.innerHTML = ''

          response.data.forEach(function (note) {
            var noteDiv = document.createElement('div')
            noteDiv.classList.add('note-item')
            noteDiv.setAttribute('data-note-id', note.id)

            // Safely decode the note text
            var noteTextDecoded = note.note_text
              .replace(/\\'/g, "'")
              .replace(/\\"/g, '"')
              .replace(/\\\\/g, '\\')

            function getFirstTenWords (str) {
              const words = str.split(' ')
              const firstTenWords = words.slice(0, 10).join(' ')
              return words.length > 10 ? `${firstTenWords}...` : firstTenWords
            }

            noteTextTitle = getFirstTenWords(noteTextDecoded)

            const createdText =
              typeof iqbible_ajax !== 'undefined' &&
              iqbible_ajax.i18n &&
              iqbible_ajax.i18n.created
                ? iqbible_ajax.i18n.created
                : 'Created:' // Add 'created' key
            const updatedText =
              typeof iqbible_ajax !== 'undefined' &&
              iqbible_ajax.i18n &&
              iqbible_ajax.i18n.updated
                ? iqbible_ajax.i18n.updated
                : 'Updated:' // Add 'updated' key

            noteDiv.innerHTML = `  
                         <hr> 
                          <div class="note-title">${noteTextTitle}</div>            
                            <div class="note-content" style='display:none;'>${noteTextDecoded}</div>

                          <small>${createdText} ${note.created_at} | ${updatedText} ${note.updated_at}</small>
                            
                            <button class="edit-note-btn" data-note-id="${note.id}">${iqbible_ajax.i18n.edit}</button></button>
                            <button class="delete-note-btn" data-note-id="${note.id}">${iqbible_ajax.i18n.delete}</button>
                           
                        `
            notesList.appendChild(noteDiv)
          })

          document.querySelectorAll('.edit-note-btn').forEach(function (btn) {
            btn.addEventListener('click', editNote)
          })

          document.querySelectorAll('.delete-note-btn').forEach(function (btn) {
            btn.addEventListener('click', deleteNote)
          })
        } else {
          document.getElementById('iqbible-notes-list').innerHTML =
            '<p>' + iqbible_ajax.i18n.noNotesFound + '</p>'
        }
      }
    }

    xhr.send(
      'action=iq_bible_get_saved_notes&security=' +
        encodeURIComponent(iqbible_ajax.nonce)
    )
  }

  function editNote () {
    var noteId = this.getAttribute('data-note-id')
    var noteContent =
      this.closest('.note-item').querySelector('.note-content').innerHTML

    tinymce.get('iqbible_editor').setContent(noteContent)
    currentNoteId = noteId

    const updateNoteText =
      typeof iqbible_ajax !== 'undefined' &&
      iqbible_ajax.i18n &&
      iqbible_ajax.i18n.updateNote
        ? iqbible_ajax.i18n.updateNote
        : 'Update Note'
    saveNoteBtn.textContent = updateNoteText

    // Scroll to the top to focus on note
    document.getElementById('iqbible-main').scrollIntoView({
      behavior: 'smooth'
    })

    cancelNoteBtn.style.display = 'inline-block'
  }

  function deleteNote () {
    var noteId = this.getAttribute('data-note-id')

    if (confirm(iqbible_ajax.i18n.confirmDeleteNote)) {
      var xhr = new XMLHttpRequest()
      xhr.open('POST', iqbible_ajax.ajaxurl, true)
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

      xhr.onload = function () {
        if (xhr.status === 200) {
          var response = JSON.parse(xhr.responseText)
          if (response.success) {
            loadSavedNotes()
            resetEditor()
          } else {
            showMessageDialog(
              iqbible_ajax.i18n.errorDeletingNote + ' ' + response.error,
              'error'
            )
          }
        }
      }

      xhr.send(
        'action=iq_bible_delete_note&note_id=' +
          encodeURIComponent(noteId) +
          '&security=' +
          encodeURIComponent(iqbible_ajax.nonce)
      )
    }
  }
})

// Clear any URL params
// ------------------------
function clearUrlParams () {
  // Check if the URL has any parameters
  if (window.location.search) {
    // Remove query params by setting window location with just the pathname
    const urlWithoutParams =
      window.location.protocol +
      '//' +
      window.location.host +
      window.location.pathname
    window.history.replaceState({}, document.title, urlWithoutParams)
  }
}

// Commentary
// ------------------------
function showCommentary (verseId) {
  var xhr = new XMLHttpRequest()
  xhr.open('POST', iqbible_ajax.ajaxurl, true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

  xhr.onload = function () {
    if (xhr.status === 200) {
      var response = JSON.parse(xhr.responseText)

      const noCommentaryText =
        typeof iqbible_ajax !== 'undefined' &&
        iqbible_ajax.i18n &&
        iqbible_ajax.i18n.noCommentary
          ? iqbible_ajax.i18n.noCommentary
          : 'No commentary available for this verse.' // Add 'noCommentary' key
      var commentaryContent = response.commentary || noCommentaryText

      // Insert commentary content into the dialog
      document.getElementById('commentary-text').innerHTML = commentaryContent

      // Open the commentary dialog
      document.getElementById('commentary-dialog').showModal()
    }
  }

  xhr.send(
    'action=iq_bible_commentary_ajax_handler&verseId=' +
      encodeURIComponent(verseId) +
      '&security=' +
      encodeURIComponent(iqbible_ajax.nonce)
  )
}

// Save verse
// ---------------
function saveVerse (verseId) {
  var messageDiv = document.getElementById('verse-message-' + verseId)

  // Check if the user is logged in
  if (iqbible_ajax.isUserLoggedIn !== '1') {
    if (messageDiv) {
      showMessageDialog(iqbible_ajax.i18n.loginToSave, 'error')

      setTimeout(() => {
        messageDiv.innerHTML = ''
      }, 3000)
    }
    return // Exit the function early if not logged in
  }

  var xhr = new XMLHttpRequest()
  xhr.open('POST', iqbible_ajax.ajaxurl, true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

  xhr.onload = function () {
    if (xhr.status === 200) {
      var response = JSON.parse(xhr.responseText)
      var messageDiv = document.getElementById('verse-message-' + verseId)
      var verseElement = document.getElementById('verse-' + verseId)

      if (response.success) {
        // Update message
        if (messageDiv) {
          showMessageDialog(iqbible_ajax.i18n.verseSaved, 'success', 3000)
        }

        // Add saved icon after the verse
        if (verseElement) {
          // Remove any existing saved icon first
          var existingIcon = verseElement.querySelector('.saved-icon')
          if (!existingIcon) {
            var savedIcon = document.createElement('img')
            savedIcon.src = iqbible_ajax.plugin_url + 'assets/img/bookmark.svg'

            const savedAltText =
              typeof iqbible_ajax !== 'undefined' &&
              iqbible_ajax.i18n &&
              iqbible_ajax.i18n.savedAlt
                ? iqbible_ajax.i18n.savedAlt
                : 'Saved!'
            savedIcon.alt = savedAltText

            savedIcon.classList.add('saved-icon')

            const verseSavedTitle =
              typeof iqbible_ajax !== 'undefined' &&
              iqbible_ajax.i18n &&
              iqbible_ajax.i18n.verseSaved
                ? iqbible_ajax.i18n.verseSaved
                : 'Verse saved!'
            savedIcon.title = verseSavedTitle

            // Insert the saved icon after the verse content
            verseElement.appendChild(savedIcon)
          }
        }
      } else {
        // Update message
        if (messageDiv) {
          showMessageDialog(iqbible_ajax.i18n.verseAlreadySaved, 'info')

          setTimeout(() => {
            messageDiv.textContent = ''
          }, 3000)
        }
      }
    }
  }

  // Get verse element and its data
  var verseElement = document.getElementById('verse-' + verseId)
  var versionId = verseElement ? verseElement.dataset.versionId : null
  var verseText = verseElement
    ? verseElement.querySelector('.copyable-text').textContent
    : ''

  // Send the verse text along with other data
  xhr.send(
    'action=iq_bible_save_verse&verseId=' +
      encodeURIComponent(verseId) +
      '&versionId=' +
      encodeURIComponent(versionId) +
      '&verseText=' +
      encodeURIComponent(verseText) +
      '&security=' +
      encodeURIComponent(iqbible_ajax.nonce)
  )
}

// Listen for clicks on the Profile tab to run our loadSavedVerses()
// ------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
  document
    .querySelector('button[onclick="openTab(\'profile\')"]')
    .addEventListener('click', function () {
      if (iqbible_ajax.isUserLoggedIn == '1') {
        // is logged in
        loadSavedVerses()
      }
    })
})

// Updated JavaScript for loading, sorting, and deleting verses
function loadSavedVerses () {
  var xhr = new XMLHttpRequest()
  xhr.open('POST', iqbible_ajax.ajaxurl, true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

  xhr.onload = function () {
    if (xhr.status === 200) {
      var response = JSON.parse(xhr.responseText)
      var savedVersesContainer = document.querySelector('.my-saved-verses')
      savedVersesContainer.innerHTML = '' // Clear previous content

      if (response.success && response.savedVerses.length > 0) {
        // Add sort controls
        var sortControls = document.createElement('div')

        const dateNewText =
          typeof iqbible_ajax !== 'undefined' &&
          iqbible_ajax.i18n &&
          iqbible_ajax.i18n.sortDateNew
            ? iqbible_ajax.i18n.sortDateNew
            : 'Date (Newest First)'

        const dateOldText =
          typeof iqbible_ajax !== 'undefined' &&
          iqbible_ajax.i18n &&
          iqbible_ajax.i18n.sortDateOld
            ? iqbible_ajax.i18n.sortDateOld
            : 'Date (Oldest First)'

        const biblicalOrderText =
          typeof iqbible_ajax !== 'undefined' &&
          iqbible_ajax.i18n &&
          iqbible_ajax.i18n.sortBiblical
            ? iqbible_ajax.i18n.sortBiblical
            : 'Biblical Order'

        sortControls.innerHTML = `
                    <select id="verse-sort" onchange="sortVerses(this.value)">

<option value="date-new">${dateNewText}</option>

<option value="date-old">${dateOldText}</option>

<option value="biblical">${biblicalOrderText}</option>

                    </select><p></p>
                `
        savedVersesContainer.appendChild(sortControls)

        // Create verses container
        var versesContainer = document.createElement('div')
        versesContainer.id = 'verses-container'
        savedVersesContainer.appendChild(versesContainer)

        // Store verses globally for sorting
        window.savedVerses = response.savedVerses
        displayVerses('date-new') // Default sort
      } else {
        savedVersesContainer.innerHTML =
          '<p>' + iqbible_ajax.i18n.noSavedVerses + '</p>'
      }
    }
  }

  xhr.send(
    'action=iq_bible_get_saved_verses&security=' +
      encodeURIComponent(iqbible_ajax.nonce)
  )
}

function displayVerses (sortOrder) {
  var verses = window.savedVerses
  var versesContainer = document.getElementById('verses-container')

  versesContainer.innerHTML = ''

  // Sort verses based on selected order
  verses.sort(function (a, b) {
    switch (sortOrder) {
      case 'date-new':
        return new Date(b.savedAt) - new Date(a.savedAt)
      case 'date-old':
        return new Date(a.savedAt) - new Date(b.savedAt)
      case 'biblical':
        // Sort by bookId, then chapter, then verse
        if (a.bookId !== b.bookId)
          return parseInt(a.bookId) - parseInt(b.bookId)
        if (a.chapter !== b.chapter)
          return parseInt(a.chapter) - parseInt(b.chapter)
        return parseInt(a.verseNumber) - parseInt(b.verseNumber)
      default:
        return 0
    }
  })

  // verses.forEach(function (verse) {
  //   var verseElement = document.createElement('div')
  //   verseElement.className = 'saved-verse'
  //   var formattedDate = new Date(verse.savedAt).toLocaleDateString()

  //   verseElement.innerHTML = `
  //           <div class="verse-content">
  //               <div class="verse-text">
  //                   ${verse.verseText} - ${verse.bookName}:${parseInt(
  //     verse.verseNumber,
  //     10
  //   )}
  //                   <span class="version-id">(${verse.versionId.toUpperCase()})</span>
  //               </div>

  //               <div class="saved-date"><small>${
  //                 iqbible_ajax.i18n.savedOn
  //               } ${formattedDate}</small></div>

  //               <button onclick="deleteVerse('${
  //                 verse.verseId
  //               }')" class="delete-verse">${iqbible_ajax.i18n.remove}</button>
  //               <p></p>
  //           </div>
  //       `

  //   versesContainer.appendChild(verseElement)
  // }

  verses.forEach(function (verse) {
    const verseElement = document.createElement('div')
    verseElement.className = 'saved-verse'
    verseElement.dataset.verseId = verse.verseId // Store ID for deletion

    const contentDiv = document.createElement('div')
    contentDiv.className = 'verse-content'

    const textDiv = document.createElement('div')
    textDiv.className = 'verse-text'
    // Escape text content before setting
    textDiv.textContent = `${verse.verseText} - ${escapeHTML(
      verse.bookName
    )}:${parseInt(verse.verseNumber, 10)} ` // Use helper

    const versionSpan = document.createElement('span')
    versionSpan.className = 'version-id'
    versionSpan.textContent = `(${escapeHTML(verse.versionId.toUpperCase())})` // Use helper
    textDiv.appendChild(versionSpan)

    const dateDiv = document.createElement('div')
    dateDiv.className = 'saved-date'
    const dateSmall = document.createElement('small')
    const formattedDate = new Date(verse.savedAt).toLocaleDateString()
    dateSmall.textContent = `${iqbible_ajax.i18n.savedOn} ${formattedDate}`
    dateDiv.appendChild(dateSmall)

    const deleteButton = document.createElement('button')
    deleteButton.className = 'delete-verse'
    deleteButton.textContent = iqbible_ajax.i18n.remove
    // Use addEventListener instead of inline onclick
    deleteButton.addEventListener('click', function () {
      deleteVerse(verse.verseId) // Pass the ID directly
    })

    const paragraphBreak = document.createElement('p') // Add spacing if needed

    contentDiv.appendChild(textDiv)
    contentDiv.appendChild(dateDiv)
    contentDiv.appendChild(deleteButton)
    contentDiv.appendChild(paragraphBreak)
    verseElement.appendChild(contentDiv)
    versesContainer.appendChild(verseElement)
  })
}

// Simple HTML escaping helper function for JavaScript
function escapeHTML (str) {
  const div = document.createElement('div')
  div.textContent = str
  return div.innerHTML
}

function sortVerses (sortOrder) {
  displayVerses(sortOrder)
}

// Delete Verse
// --------------
function deleteVerse (verseId) {
  if (!confirm(iqbible_ajax.i18n.confirmDeleteVerse)) {
    return
  }

  var xhr = new XMLHttpRequest()
  xhr.open('POST', iqbible_ajax.ajaxurl, true)
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')

  xhr.onload = function () {
    if (xhr.status === 200) {
      var response = JSON.parse(xhr.responseText)
      if (response.success) {
        // Remove verse from the global array
        window.savedVerses = window.savedVerses.filter(function (verse) {
          return verse.verseId !== verseId
        })

        // Find the verse element by ID
        var verseElement = document.getElementById('verse-' + verseId)

        // If the verse element exists, remove the saved icon
        if (verseElement) {
          var existingIcon = verseElement.querySelector('.saved-icon')
          if (existingIcon) {
            verseElement.removeChild(existingIcon)
          }
        }

        // Refresh the display with current sort order
        var currentSort = document.getElementById('verse-sort').value
        displayVerses(currentSort)

        if (window.savedVerses.length === 0) {
          const noSavedText =
            typeof iqbible_ajax !== 'undefined' &&
            iqbible_ajax.i18n &&
            iqbible_ajax.i18n.noSavedVerses
              ? iqbible_ajax.i18n.noSavedVerses
              : 'No saved verses.'
          document.querySelector('.my-saved-verses').innerHTML =
            '<p>' + noSavedText + '</p>'
        }
      } else {
        showMessageDialog(
          iqbible_ajax.i18n.errorRemovingVerse + ' ' + response.error,
          'error'
        )
      }
    } else {
      showMessageDialog(iqbible_ajax.i18n.errorRemovingVerseRetry, 'error')
    }
  }

  xhr.send(
    'action=iq_bible_delete_saved_verse&verseId=' +
      encodeURIComponent(verseId) +
      '&security=' +
      encodeURIComponent(iqbible_ajax.nonce)
  )
}

// Share verse
// ---------------
function shareVerse (verseId) {
  // Try to find the button directly with the exact onclick attribute
  var buttons = document.querySelectorAll('button')
  var targetButton = null

  // Inspect all buttons to find the one with the matching onclick
  for (var i = 0; i < buttons.length; i++) {
    var onclickAttr = buttons[i].getAttribute('onclick')
    if (onclickAttr && onclickAttr.includes(`shareVerse('${verseId}')`)) {
      targetButton = buttons[i]
      break
    }
  }

  console.log('Target button found:', targetButton)

  // Get the data-url attribute if the button was found
  var verseUrl = ''
  if (targetButton) {
    verseUrl = targetButton.getAttribute('data-url')
    console.log('Found data-url:', verseUrl)
  }

  // If we have a valid URL from the button
  if (verseUrl) {
    verseUrl = verseUrl.replace(/&amp;/g, '&')

    try {
      const url = new URL(verseUrl)
      // Set the verseId parameter with the desired format
      url.searchParams.set('verseId', 'verse-' + verseId)
      url.hash = ''
      verseUrl = url.toString()
    } catch (error) {
      console.error('Error processing URL:', error)
      if (verseUrl.includes('?')) {
        verseUrl += '&verseId=verse-' + verseId
      } else {
        verseUrl += '?verseId=verse-' + verseId
      }
    }
  } else {
    // Fallback - get current page URL and add verseId
    var currentUrl = new URL(window.location.href)
    currentUrl.searchParams.set('verseId', 'verse-' + verseId)
    verseUrl = currentUrl.toString()
    console.log('Using fallback URL:', verseUrl)
  }

  console.log('Final URL to copy:', verseUrl)

  // Copy to clipboard
  if (navigator.clipboard && window.isSecureContext) {
    navigator.clipboard
      .writeText(verseUrl)
      .then(() => {
        console.log('URL copied to clipboard successfully')
        var messageDiv = document.getElementById('verse-message-' + verseId)
        if (messageDiv) {
          showMessageDialog(iqbible_ajax.i18n.linkCopied, 'success', 3000)
        }
      })
      .catch(error => {
        console.error('Clipboard error:', error)
        showMessageDialog(iqbible_ajax.i18n.errorCopyLink, 'error')
      })
  }
}
