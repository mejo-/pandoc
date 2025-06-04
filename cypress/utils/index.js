/**
 * SPDX-FileCopyrightText: 2021 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { User } from '@nextcloud/cypress' // eslint-disable-line

export const randHash = () => Math.random().toString(36).replace(/[^a-z]+/g, '').slice(0, 10)
export const randUser = () => new User(randHash())
